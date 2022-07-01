<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Exporter;

use Contao\File;
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

    public function export(\stdClass $config, $ids = null): File
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

        $writer->setRowCallback(fn ($data) => $this->dataTransformer->compileRow($data, $config, $columnConfig));

        $this->handleDefaultExportResult($writer->writeFrom($reader));

        $this->updateLastRun($config);

        return new File($writer->getFilename());
    }
}
