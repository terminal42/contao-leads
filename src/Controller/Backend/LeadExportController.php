<?php

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\Controller;
use Contao\Input;
use Terminal42\LeadsBundle\Export\ExportFactory;

class LeadExportController
{
    public function __construct(ExportFactory $exportFactory)
    {
        $this->exportFactory = $exportFactory;
    }

    public function __invoke()
    {
        $configId = (int) Input::get('config');

        if (!$configId) {
            Controller::redirect('contao/main.php?act=error');
        }

        $config = $this->exportFactory->buildConfig($configId);
        $arrIds = is_array($GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root']) ? $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] : null;

        $file = $this->exportFactory->createForType($config->type)->export($config, $arrIds);
        $file->sendToBrowser();
    }
}
