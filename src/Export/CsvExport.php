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

use Haste\IO\Reader\ArrayReader;
use Haste\IO\Writer\CsvFileWriter;
use Terminal42\LeadsBundle\Export\Utils\File;
use Terminal42\LeadsBundle\Export\Utils\Row;

class CsvExport extends AbstractExport
{
    /**
     * Returns true if available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    public function export(\stdClass $config, $ids = null): \Contao\File
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

        $this->handleDefaultExportResult($writer->writeFrom($reader));

        $this->updateLastRun($config);

        return new \Contao\File($writer->getFilename());
    }
}
