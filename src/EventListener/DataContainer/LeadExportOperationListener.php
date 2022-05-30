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

use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\Routing\RouterInterface;
use Terminal42\LeadsBundle\Model\LeadExport;

class LeadExportOperationListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onLoadCallback(): void
    {
        $configs = null;
        if (($formId = (int)Input::get('master')) > 0) {
            /** @var LeadExport[] $configs */
            $configs = LeadExport::findByPid($formId);
        }

        if (null === $configs) {
            return;
        }

        $operations = [];

        foreach ($configs as $config) {
            $operations[] = [
                'label' => $config->name,
                'class' => 'leads-export header_export_excel',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => function ($href, $label, $title, $class, $attributes) use ($config) {
                    return sprintf(
                        '<a href="%s" class="%s" title="%s"%s>%s</a> ',
                        $this->router->generate('terminal42_leads.export', ['id' => (int)$config->id]),
                        $class,
                        StringUtil::specialchars($title),
                        $attributes,
                        $label
                    );
                }
            ];
        }

        array_unshift($GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'], ...$operations);
    }
}
