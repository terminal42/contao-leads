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
use Leads\Export;
use Leads\Exporter\Utils\File;

abstract class AbstractExcelExporter extends AbstractExporter
{
    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return class_exists('PHPExcel');
    }

    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param \Database_Result $config
     * @param array|null       $ids
     */
    public function export(\Database_Result $config, $ids = null)
    {
        throw new \RuntimeException('export() has to be implemented by a child class of AbstractExcelExporter.');
    }


    /**
     * Exports based on Excel format.
     *
     * @param \Database_Result $config
     * @param array|null       $ids
     * @param string           $format
     */
    protected function exportWithFormat($config, $ids, $format)
    {
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);
        $reader = new ArrayReader($dataCollector->getExportData());
        $writer = new ExcelFileWriter('system/tmp/' . File::getName($config));
        $writer->setFormat($format);

        // Add header fields
        if ($config->headerFields) {
            $reader->setHeaderFields($this->prepareHeaderFields($config, $dataCollector));
            $writer->enableHeaderFields();
        }

        $writer->setRowCallback(function($arrData) use ($config) {
            return Export::generateExportRow($arrData, $config);
        });

        if (!$writer->writeFrom($reader)) {
            $objResponse = new Response('Data export failed.', 500);
            $objResponse->send();
        }

        $objFile = new \File($writer->getFilename());
        $objFile->sendToBrowser();
    }
}