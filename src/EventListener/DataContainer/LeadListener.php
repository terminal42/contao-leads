<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\Controller;
use Contao\Input;
use Contao\System;
use Haste\Util\StringUtil;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Terminal42\LeadsBundle\Exporter\ExporterFactory;
use Terminal42\LeadsBundle\Util\NotificationCenter;

class LeadListener
{
    /**
     * @var NotificationCenter
     */
    private $notificationCenter;

    /**
     * @var ExporterFactory
     */
    private $exportFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(NotificationCenter $notificationCenter, ExporterFactory $exportFactory, RouterInterface $router, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->notificationCenter = $notificationCenter;
        $this->exportFactory = $exportFactory;
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onLoadCallback(): void
    {
        if (!$this->authorizationChecker->isGranted('lead_form', (int) Input::get('master'))) {
            $exception = new AccessDeniedException('Not enough permissions to access leads ID "'.Input::get('master').'"');
            $exception->setAttributes('lead_form');
            $exception->setSubject(Input::get('master'));

            throw $exception;
        }

        $this->addNotificationCenterSupport();
    }

    /**
     * Generate label for this record.
     *
     * @param array
     * @param string
     *
     * @return string
     */
    public function onLabelCallback($row, $label)
    {
        $objForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')->execute($row['master_id']);

        // No form found, we can't format the label
        if (!$objForm->numRows) {
            return $label;
        }

        $arrTokens = [
            'created' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['created']),
        ];

        $objData = \Database::getInstance()->prepare('SELECT * FROM tl_lead_data WHERE pid=?')->execute($row['id']);

        while ($objData->next()) {
            StringUtil::flatten(StringUtil::deserialize($objData->value), $objData->name, $arrTokens);
        }

        return StringUtil::recursiveReplaceTokensAndTags($objForm->leadLabel, $arrTokens);
    }

    /**
     * Return the export config icon.
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     */
    public function onExportButtonCallback($href, $label, $title, $class, $attributes)
    {
        $user = \BackendUser::getInstance();

        if (!$user->isAdmin && !$user->canEditFieldsOf('tl_lead_export')) {
            return '';
        }

        return '<a href="contao/main.php?do=form&amp;table=tl_lead_export&amp;id='.Input::get('master').'" class="'.$class.'" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }

    public function onShowButtonCallback(array $row, $href, $label, $title, $icon, $attributes, $table)
    {
        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.openModalIframe({\'title\':\'%s\',\'url\':this.href});return false"%s>%s</a> ',
            $this->router->generate('terminal42_leads.details', ['id' => $row['id'], 'popup' => 1]),
            \StringUtil::specialchars($title),
            \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG'][$table]['show'][1], $row['id']))),
            $attributes,
            \Image::getHtml($icon, $label)
        );
    }

    /**
     * Adds the buttons to the buttons bar and exports the data if it is an export button.
     *
     * @param array $arrButtons
     */
    public function onSelectButtonsCallback($arrButtons)
    {
        $arrConfigs = \Database::getInstance()
                               ->prepare('SELECT id, name FROM tl_lead_export WHERE pid=? ORDER BY name')
                               ->execute(\Input::get('master'))
                               ->fetchAllAssoc()
        ;

        // Run the export
        if ('tl_select' === \Input::post('FORM_SUBMIT')) {
            $arrIds = \Input::post('IDS');

            if (empty($arrIds)) {
                Controller::reload();
            }

            if (\Input::post('notification')) {
                Controller::redirect(\Backend::addToUrl('key=notification'));
            }

            foreach ($arrConfigs as $config) {
                if (\Input::post('export_'.$config['id'])) {
                    $config = $this->exportFactory->buildConfig((int) $config['id']);
                    $file = $this->exportFactory->createForType($config->type)->export($config['id'], $arrIds);
                    $file->sendToBrowser();
                }
            }
        }

        System::loadLanguageFile('tl_lead_export');

        // Generate buttons
        foreach ($arrConfigs as $config) {
            $arrButtons['export_'.$config['id']] = '<input type="submit" name="export_'.$config['id'].'" id="export_'.$config['id'].'" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0].' "'.$config['name'].'"').'">';
        }

        // Notification Center integration
        if ($this->notificationCenter->isAvailable()) {
            $arrButtons['notification'] = '<input type="submit" name="notification" id="notification" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['notification'][0]).'">';
        }

        return $arrButtons;
    }

    /**
     * Add the notification center support.
     */
    private function addNotificationCenterSupport(): void
    {
        if (!$this->notificationCenter->isAvailable()) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_lead']['list']['operations']['notification'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_lead']['notification'],
            'href' => 'key=notification',
            'icon' => 'system/modules/notification_center/assets/notification.png',
        ];
    }
}
