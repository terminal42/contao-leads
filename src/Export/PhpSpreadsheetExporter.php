<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use Codefog\HasteBundle\StringParser;
use Contao\FilesModel;
use Doctrine\DBAL\Connection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;

#[AsLeadsExporter('xlsx')]
#[AsLeadsExporter('xls')]
#[AsLeadsExporter('excel_csv')]
#[AsLeadsExporter('ods')]
#[AsLeadsExporter('html')]
class PhpSpreadsheetExporter extends AbstractExporter
{
    public function __construct(
        private readonly string $projectDir,
        ServiceLocator $formatters,
        Connection $connection,
        TranslatorInterface $translator,
        StringParser $parser,
        ExpressionLanguage|null $expressionLanguage = null,
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
            $writerType
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
            $template = FilesModel::findByPk($config['template']);

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

        foreach ($this->iterateRows() as $data) {
            $isList = array_is_list($data);

            foreach ($data as $col => $value) {
                // Do not write empty string so columns in Excel can be skipped
                if ('' === $value) {
                    continue;
                }

                $sheet->setCellValue(
                    [$isList ? $col + 1 : $col, $row],
                    $value
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
