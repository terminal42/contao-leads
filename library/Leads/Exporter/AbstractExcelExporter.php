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


use Haste\IO\Writer\ExcelFileWriter;
use Leads\DataCollector;
use Leads\Export;

abstract class AbstractExcelExporter implements ExporterInterface
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
    abstract public function export(\Database_Result $config, $ids = null);


    /**
     * Exports based on Excel format.
     *
     * @param \Database_Result $config
     * @param array|null       $ids
     * @param string           $format
     */
    protected function exportWithFormat($config, $ids, $format)
    {
        $reader = DataCollector::fetchExportData($config, $ids);

        $writer = new ExcelFileWriter('system/tmp/' . Export::getFilename($config));
        $writer->setFormat($format);

        // Add header fields
        if ($config->headerFields) {
            $writer->enableHeaderFields();
        }

        $writer->setRowCallback(function($arrData) use ($config) {
            return Export::generateExportRow($arrData, $config);
        });

        if (!$writer->writeFrom($reader)) {
            $objResponse = new \Haste\Http\Response\Response('Data export failed.', 500);
            $objResponse->send();
        }

        $objFile = new \File($writer->getFilename());
        $objFile->sendToBrowser();
    }
}