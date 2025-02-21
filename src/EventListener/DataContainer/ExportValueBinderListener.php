<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Terminal42\LeadsBundle\Export\ExporterInterface;
use Terminal42\LeadsBundle\Export\PhpSpreadsheetExporter;

#[AsCallback('tl_lead_export', 'config.onload')]
class ExportValueBinderListener
{
    /**
     * @param ServiceLocator<ExporterInterface> $exporters
     */
    public function __construct(
        private readonly ServiceLocator $exporters,
        private readonly ServiceLocator $valueBinders,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $data = $dc->getCurrentRecord();

        if (
            $data
            && ($exporter = $this->exporters->get($data['type']))
            && !$exporter instanceof PhpSpreadsheetExporter
        ) {
            return;
        }

        $pm = PaletteManipulator::create()->addField('valueBinder', 'filename');

        foreach ($GLOBALS['TL_DCA']['tl_lead_export']['palettes'] as $k => $v) {
            if (!\is_array($v)) {
                $pm->applyToPalette($k, 'tl_lead_export');
            }
        }

        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['valueBinder']['options'] = array_keys($this->valueBinders->getProvidedServices());

        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFields']['valueBinder'] = $GLOBALS['TL_DCA']['tl_lead_export']['fields']['valueBinder'];
        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFields']['valueBinder']['eval']['includeBlankOption'] = true;
        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFields']['valueBinder']['eval']['blankOptionLabel'] = $GLOBALS['TL_LANG']['tl_lead_export']['valueBinder'][2];
        unset($GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFieds']['valueBinder']['sql']);

        $GLOBALS['TL_DCA']['tl_lead_export']['fields']['tokenFields']['eval']['columnFields']['valueBinder'] = $GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFields']['valueBinder'];
    }
}
