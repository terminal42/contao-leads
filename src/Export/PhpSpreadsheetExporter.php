<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Terminal42\LeadsBundle\DependencyInjection\Attribute\AsLeadsExporter;

#[AsLeadsExporter('xlsx')]
#[AsLeadsExporter('xls')]
#[AsLeadsExporter('excel_csv')]
#[AsLeadsExporter('ods')]
#[AsLeadsExporter('html')]
class PhpSpreadsheetExporter extends AbstractExporter
{
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

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
