<?php

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\Controller;
use Contao\Input;
use Contao\System;

class LeadListener
{
    public function onLoadCallback()
    {
        $this->loadExportConfigs();
        $this->checkPermission();
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
        $objForm = \Database::getInstance()->prepare("SELECT * FROM tl_form WHERE id=?")->execute($row['master_id']);

        // No form found, we can't format the label
        if (!$objForm->numRows) {
            return $label;
        }

        $arrTokens = array(
            'created' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['created']),
        );

        $objData = \Database::getInstance()->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

        while ($objData->next()) {
            \Haste\Util\StringUtil::flatten(deserialize($objData->value), $objData->name, $arrTokens);
        }

        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($objForm->leadLabel, $arrTokens);
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

        return '<a href="contao/main.php?do=form&amp;table=tl_lead_export&amp;id=' . Input::get('master') . '" class="'.$class.'" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }

    /**
     * Adds the buttons to the buttons bar and exports the data if it is an export button.
     *
     * @param array $arrButtons
     *
     * @return mixed
     */
    public function onSelectButtonsCallback($arrButtons)
    {
        $arrConfigs = \Database::getInstance()
                               ->prepare("SELECT id, name FROM tl_lead_export WHERE pid=? ORDER BY name")
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
                if (\Input::post('export_' . $config['id'])) {
                    $file = \Terminal42\LeadsBundle\Leads::export($config['id'], $arrIds);
                    $file->sendToBrowser();
                }
            }
        }

        System::loadLanguageFile('tl_lead_export');

        // Generate buttons
        foreach ($arrConfigs as $config) {
            $arrButtons['export_' . $config['id']] = '<input type="submit" name="export_' . $config['id'] . '" id="export_' . $config['id'] . '" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0] . ' "' . $config['name'] . '"').'">';
        }

        // Notification Center integration
        if (\Terminal42\LeadsBundle\LeadsNotification::available(true)) {
            $arrButtons['notification'] = '<input type="submit" name="notification" id="notification" class="tl_submit" value="' . specialchars($GLOBALS['TL_LANG']['tl_lead']['notification'][0]) . '">';
        }

        return $arrButtons;
    }


    /**
     * Load the export configs.
     */
    private function loadExportConfigs()
    {
        $objConfigs = \Database::getInstance()
                               ->prepare("SELECT * FROM tl_lead_export WHERE pid=? AND tstamp>0 ORDER BY name")
                               ->execute(Input::get('master'))
        ;

        if (!$objConfigs->numRows) {
            return;
        }

        $arrOperations = array();

        while ($objConfigs->next()) {
            $arrOperations['export_' . $objConfigs->id] = array(
                'label'         => $objConfigs->name,
                'href'          => 'key=export&amp;config=' . $objConfigs->id,
                'class'         => 'leads-export header_export_excel',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            );
        }

        array_insert($GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'], 0, $arrOperations);
    }


    /**
     * Check if a user has access to lead data.
     *
     * @param $dc
     */
    private function checkPermission()
    {
        if (Input::get('master') == '') {
            Controller::redirect('contao/main.php?act=error');
        }

        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            return;
        }

        if (!is_array($objUser->forms) || !in_array(\Input::get('master'), $objUser->forms)) {
            System::log('Not enough permissions to access leads ID "'.\Input::get('master').'"', __METHOD__, TL_ERROR);
            Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Add the notification center support
     */
    private function addNotificationCenterSupport()
    {
        if (!\Terminal42\LeadsBundle\LeadsNotification::available(true)) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_lead']['list']['operations']['notification'] = array(
            'label' => &$GLOBALS['TL_LANG']['tl_lead']['notification'],
            'href'  => 'key=notification',
            'icon'  => 'system/modules/notification_center/assets/notification.png',
        );
    }
}
