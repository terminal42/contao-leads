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
use Haste\IO\Writer\CsvFileWriter;
use Leads\Exporter\Utils\File;
use Leads\Exporter\Utils\Row;

class Csv extends AbstractExporter
{
    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     *
     * @throws ExportFailedException
     */
    public function export($config, $ids = null)
    {
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);

        $reader = new ArrayReader($dataCollector->getExportData());
        $writer = new CsvFileWriter('system/tmp/' . File::getName($config));

        // Add header fields
        if ($config->headerFields) {
            $reader->setHeaderFields($this->prepareDefaultHeaderFields($config, $dataCollector));
            $writer->enableHeaderFields();
        }

        $row = new Row($config, $this->prepareDefaultExportConfig($config, $dataCollector));

        $writer->setRowCallback(function($data) use ($row) {
            return $row->compile($data);
        });

        if (!$writer->writeFrom($reader)) {
            throw new ExportFailedException('Data export failed.');
        }

        $dataCollector->updateLastRun($config->id);

        $objFile = new \File($writer->getFilename());
        $objFile->sendToBrowser();
    }
}
