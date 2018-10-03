<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */
namespace Terminal42\LeadsBundle\Export;


use Haste\Http\Response\Response;
use Haste\IO\Reader\ArrayReader;
use Haste\IO\Writer\ExcelFileWriter;
use Terminal42\LeadsBundle\Export\Utils\File;
use Terminal42\LeadsBundle\Export\Utils\Row;
use PHPExcel_Cell;
use PHPExcel_IOFactory;

abstract class AbstractExcelExport extends AbstractExport
{
    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return class_exists('PHPExcel') && class_exists('PHPExcel_IOFactory');
    }

    /**
     * Exports based on Excel format.
     *
     * @param \Database\Result|object $config
     * @param array|null              $ids
     * @param string                  $format
     *
     * @return \Contao\File
     */
    protected function exportWithFormat($config, $ids, $format)
    {
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);

        $reader = new ArrayReader($dataCollector->getExportData());

        if ($config->headerFields) {
            $reader->setHeaderFields($this->prepareDefaultHeaderFields($config, $dataCollector));
        }

        $row = new Row($config, $this->prepareDefaultExportConfig($config, $dataCollector));

        if ($config->useTemplate) {
            return $this->exportWithTemplate($config, $reader, $row, $format);
        }

        return $this->exportWithoutTemplate($config, $reader, $row, $format);
    }

    /**
     * Default export without template.
     *
     * @param               $config
     * @param ArrayReader   $reader
     * @param Row           $row
     * @param               $format
     *
     * @return \Contao\File
     *
     * @throws ExportFailedException
     */
    protected function exportWithoutTemplate(
        $config,
        ArrayReader $reader,
        Row $row,
        $format
    ) {
        $writer = new ExcelFileWriter('system/tmp/' . $this->exportFile->getFilenameForConfig($config));
        $writer->setFormat($format);

        // Add header fields
        if ($config->headerFields) {
            $writer->enableHeaderFields();
        }

        $writer->setRowCallback(function($data) use ($row) {
            return $row->compile($data);
        });

        $this->handleDefaultExportResult($writer->writeFrom($reader));

        $this->updateLastRun($config);

        return new \Contao\File($writer->getFilename());
    }

    /**
     * Export with template.
     *
     * @param               $config
     * @param ArrayReader   $reader
     * @param Row           $row
     * @param               $format
     *
     * @return \Contao\File
     */
    protected function exportWithTemplate(
        $config,
        ArrayReader $reader,
        Row $row,
        $format
    ) {
        // Fetch the template and make a copy of it
        $template = \FilesModel::findByPk($config->template);

        if (null === $template) {
            $objResponse = new Response('Could not find template.', 500);
            $objResponse->send();
        }

        $tmpPath = 'system/tmp/' . $this->exportFile->getFilenameForConfig($config);
        \Files::getInstance()->copy($template->path, $tmpPath);

        $excelReader = PHPExcel_IOFactory::createReader($format);
        $excel = $excelReader->load(TL_ROOT . '/' . $tmpPath);

        $excel->setActiveSheetIndex((int) $config->sheetIndex);
        $sheet = $excel->getActiveSheet();

        $currentRow = (int) $config->startIndex ?: 1;
        $currentColumn = 0;

        foreach ($reader as $readerRow) {
            $compiledRow = $row->compile($readerRow);

            foreach ($compiledRow as $k => $value) {
                // Support explicit target column
                if ('tokens' === $config->export && isset($config->tokenFields[$k]['targetColumn'])) {
                    $column = $config->tokenFields[$k]['targetColumn'];

                    if (!is_numeric($column)) {
                        $column = PHPExcel_Cell::columnIndexFromString($column) - 1;
                    }
                } else {
                    // Use next column, ignoring explicit target columns in the counter
                    $column = $currentColumn++;
                }

                $sheet->setCellValueExplicitByColumnAndRow(
                    $column,
                    $currentRow,
                    (string) $value,
                    \PHPExcel_Cell_DataType::TYPE_STRING2
                );
            }

            $currentColumn = 0;
            $currentRow++;
        }

        $excelWriter = \PHPExcel_IOFactory::createWriter($excel, $format);
        $excelWriter->save(TL_ROOT . '/' . $tmpPath);

        $this->updateLastRun($config);

        return new \Contao\File($tmpPath);
    }
}
