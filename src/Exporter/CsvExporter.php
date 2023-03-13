<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Exporter;

use Contao\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class CsvExporter extends AbstractExcelExporter
{
    /**
     * @throws \Exception
     */
    public function export(\stdClass $config, $ids = null): File
    {
        $sheet = new Spreadsheet();
        $sheet = $this->writeDataToSheet($sheet, $config, $ids);

        $writer = new Csv($sheet);
        $writer->setUseBOM(true);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        return $this->exportWriter($writer, $config);
    }
}
