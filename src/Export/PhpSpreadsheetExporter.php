<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use Codefog\HasteBundle\StringParser;
use Contao\FilesModel;
use Doctrine\DBAL\Connection;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;
use Terminal42\LeadsBundle\Export\Format\FormatterInterface;

#[AsLeadsExporter('xlsx')]
#[AsLeadsExporter('xls')]
#[AsLeadsExporter('excel_csv')]
#[AsLeadsExporter('ods')]
#[AsLeadsExporter('html')]
class PhpSpreadsheetExporter extends AbstractExporter
{
    /**
     * @param ServiceLocator<IValueBinder>       $valueBinders
     * @param ServiceLocator<FormatterInterface> $formatters
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly ServiceLocator $valueBinders,
        ServiceLocator $formatters,
        Connection $connection,
        TranslatorInterface $translator,
        StringParser $parser,
        ExpressionLanguage $expressionLanguage,
    ) {
        parent::__construct($formatters, $connection, $translator, $parser, $expressionLanguage);
    }

    protected function doExport($stream): void
    {
        $writerType = match ($this->getConfig()['type']) {
            'excel_csv' => 'Csv',
            default => ucfirst($this->getConfig()['type']),
        };

        $writer = IOFactory::createWriter(
            $this->generateSpreadsheet(),
            $writerType,
        );

        if ($writer instanceof Csv) {
            $writer->setExcelCompatibility(true);
        }

        $writer->save($stream);
    }

    protected function generateSpreadsheet(): Spreadsheet
    {
        $config = $this->getConfig();

        if ($config['useTemplate']) {
            $template = FilesModel::findById($config['template']);

            if (null === $template) {
                throw new \RuntimeException('Could not find export template.');
            }

            $spreadsheet = IOFactory::load($this->projectDir.'/'.$template->path);
            $spreadsheet->setActiveSheetIndex((int) $config['sheetIndex']);
            $row = (int) $config['startIndex'] ?: 1;
        } else {
            $spreadsheet = new Spreadsheet();
            $row = 1;
        }

        $sheet = $spreadsheet->getActiveSheet();

        if ($this->valueBinders->has($config['valueBinder'])) {
            $spreadsheet->setValueBinder($this->valueBinders->get($config['valueBinder']));
        }

        foreach ($this->iterateRows(false, true) as $data) {
            $isList = array_is_list($data);

            foreach ($data as $col => $config) {
                $value = $config['value'];

                // Do not write empty string so columns in Excel can be skipped
                if ('' === $value) {
                    continue;
                }

                $sheet->setCellValue(
                    [$isList ? $col + 1 : $col, $row],
                    $value,
                    $this->valueBinders->has($config['valueBinder'] ?? '') ? $this->valueBinders->get($config['valueBinder']) : null,
                );
            }

            ++$row;
        }

        return $spreadsheet;
    }

    protected function getFileExtension(): string
    {
        $type = $this->getConfig()['type'];

        return match ($type) {
            'tcpdf', 'dompdf', 'mpdf' => '.pdf',
            'excel_csv' => '.csv',
            default => '.'.$type,
        };
    }
}
