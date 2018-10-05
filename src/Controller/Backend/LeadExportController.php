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

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\Controller;
use Contao\Input;
use Terminal42\LeadsBundle\Exporter\ExporterFactory;

class LeadExportController
{
    public function __construct(ExporterFactory $exportFactory)
    {
        $this->exportFactory = $exportFactory;
    }

    public function __invoke(): void
    {
        $configId = (int) Input::get('config');

        if (!$configId) {
            Controller::redirect('contao/main.php?act=error');
        }

        $config = $this->exportFactory->buildConfig($configId);
        $arrIds = \is_array($GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root']) ? $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] : null;

        $file = $this->exportFactory->createForType($config->type)->export($config, $arrIds);
        $file->sendToBrowser();
    }
}
