<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */
namespace Leads\Exporter;


use Haste\Http\Response\Response;
use Haste\IO\Reader\ArrayReader;
use Haste\IO\Writer\ExcelFileWriter;
use Leads\DataCollector;
use Leads\Exporter\Utils\File;
use Leads\Exporter\Utils\Row;
use PHPExcel_Cell;
use PHPExcel_IOFactory;

abstract class AbstractExcelExporter extends AbstractExporter
{
    private $dataCollector;

    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return class_exists('PHPExcel') && class_exists('PHPExcel_IOFactory');
    }

    /**
     * Exports based on Excel format.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     * @param string           $format
     */
    protected function exportWithFormat($config, $ids, $format)
    {
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);
        $dataCollector->setUseTableLocking(true);

        $reader = new ArrayReader($dataCollector->getExportData());

        if ($config->headerFields) {
            $reader->setHeaderFields($this->prepareDefaultHeaderFields($config, $dataCollector));
        }

        $row = new Row($config, $this->prepareDefaultExportConfig($config, $dataCollector));

        if ($config->useTemplate) {
            $this->exportWithTemplate($config, $reader, $row, $format, $dataCollector);
        } else {
            $this->exportWithoutTemplate($config, $reader, $row, $format, $dataCollector);
        }
    }

    /**
     * Default export without template.
     *
     * @param               $config
     * @param ArrayReader   $reader
     * @param Row           $row
     * @param               $format
     * @param DataCollector $dataCollector
     */
    protected function exportWithoutTemplate(
        $config,
        ArrayReader $reader,
        Row $row,
        $format,
        DataCollector $dataCollector
    ) {
        $writer = new ExcelFileWriter('system/tmp/' . File::getName($config));
        $writer->setFormat($format);

        // Add header fields
        if ($config->headerFields) {
            $writer->enableHeaderFields();
        }

        $writer->setRowCallback(function($data) use ($row) {
            return $row->compile($data);
        });

        if (!$writer->writeFrom($reader)) {
            $dataCollector->unlockTables();

            $objResponse = new Response('Data export failed.', 500);
            $objResponse->send();
        }

        $dataCollector->updateLastRun($config->id);

        $objFile = new \File($writer->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Export with template.
     *
     * @param               $config
     * @param ArrayReader   $reader
     * @param Row           $row
     * @param               $format
     * @param DataCollector $dataCollector
     */
    protected function exportWithTemplate($config, ArrayReader $reader, Row $row, $format, DataCollector $dataCollector)
    {
        // Fetch the template and make a copy of it
        $template = \FilesModel::findByPk($config->template);

        if (null === $template) {
            $objResponse = new Response('Could not find template.', 500);
            $objResponse->send();
        }

        $tmpPath = 'system/tmp/' . File::getName($config);
        \Files::getInstance()->copy($template->path, $tmpPath);

        $excelReader = PHPExcel_IOFactory::createReader($format);
        $excel = $excelReader->load(TL_ROOT . '/' . $tmpPath);

        $excel->setActiveSheetIndex(0);

        $currentRow = (int) $config->startIndex;
        $currentColumn = 0;

        foreach ($reader as $readerRow) {
            $compiledRow = $row->compile($readerRow);

            foreach ($compiledRow as $k => $value) {
                $specificColumn = null;

                // Support explicit target column
                if ($config->export == 'tokens'
                    && isset($config->tokenFields[$k]['targetColumn'])
                ) {
                    $specificColumn = $config->tokenFields[$k]['targetColumn'];

                    if (!is_numeric($specificColumn)) {
                        $specificColumn = PHPExcel_Cell::columnIndexFromString($specificColumn) - 1;
                    }
                }

                $excel->getActiveSheet()->setCellValueExplicitByColumnAndRow(
                    ($specificColumn) ?: $currentColumn++,
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

        $dataCollector->updateLastRun($config->id);

        $tmpFile = new \File($tmpPath);
        $tmpFile->sendToBrowser();
    }
}
