<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Exporter;

use Haste\IO\Reader\ArrayReader;
use Haste\IO\Writer\CsvFileWriter;

class CsvExporter extends AbstractExporter
{
    /**
     * Returns true if available.
     */
    public function isAvailable(): bool
    {
        return true;
    }

    public function export(\stdClass $config, $ids = null): \Contao\File
    {
        $dataCollector = $this->prepareDefaultDataCollector($config, $ids);

        $reader = new ArrayReader($dataCollector->getExportData());
        $writer = new CsvFileWriter('system/tmp/'.$this->exportFile->getFilenameForConfig($config));

        // Add header fields
        if ($config->headerFields) {
            $reader->setHeaderFields($this->prepareDefaultHeaderFields($config, $dataCollector));
            $writer->enableHeaderFields();
        }

        $columnConfig = $this->prepareDefaultExportConfig($config, $dataCollector);

        $writer->setRowCallback(function ($data) use ($config, $columnConfig) {
            return $this->dataTransformer->compileRow($data, $config, $columnConfig);
        });

        $this->handleDefaultExportResult($writer->writeFrom($reader));

        $this->updateLastRun($config);

        return new \Contao\File($writer->getFilename());
    }
}
