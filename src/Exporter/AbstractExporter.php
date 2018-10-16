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

use Terminal42\LeadsBundle\DataCollector;
use Terminal42\LeadsBundle\Model\Lead;
use Terminal42\LeadsBundle\Util\DataTransformer;
use Terminal42\LeadsBundle\Util\ExportFile;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var DataTransformer
     */
    protected $dataTransformer;

    /**
     * @var ExportFile
     */
    protected $exportFile;

    /**
     * Last run that will be updated.
     *
     * @var int
     */
    protected $newLastRun;

    public function __construct(DataTransformer $dataTransformer, ExportFile $exportFile)
    {
        $this->dataTransformer = $dataTransformer;
        $this->exportFile = $exportFile;
    }

    public function getType(): string
    {
        $className = \get_called_class();
        $className = substr($className, strrpos($className, '\\') + 1);

        if ('Exporter' === substr($className, -8)) {
            $className = substr($className, 0, -8);
        }

        return lcfirst($className);
    }

    public function getLabel(): string
    {
        return $GLOBALS['TL_LANG']['tl_lead_export']['type'][$this->getType()] ?? $this->getType();
    }

    /**
     * Prepares the default DataCollector instance based on the configuration.
     *
     * @param \stdClass  $config
     * @param array|null $ids
     *
     * @return DataCollector
     */
    protected function prepareDefaultDataCollector($config, $ids = null)
    {
        $dataCollector = new DataCollector($config->master);

        // Limit the fields
        if ('fields' === $config->export) {
            $limitFields = [];

            foreach ($config->fields as $fieldsConfig) {
                $limitFields[] = $fieldsConfig['field'];
            }

            $dataCollector->setFieldIds($limitFields);
        }

        if (null !== $ids) {
            $dataCollector->setLeadDataIds($ids);
        }

        $this->newLastRun = \Date::floorToMinute();

        if ($config->skipLastRun) {
            $dataCollector->setFrom($config->lastRun);
            $dataCollector->setTo($this->newLastRun - 1);
        }

        return $dataCollector;
    }

    /**
     * Prepares the header fields according to the configuration.
     *
     * @param \Database\Result|object $config
     *
     * @return array
     */
    protected function prepareDefaultHeaderFields($config, DataCollector $dataCollector)
    {
        $headerFields = [];
        $systemColumns = Lead::getSystemColumns();

        // Config: all
        if ('all' === $config->export) {
            foreach ($systemColumns as $systemColumn) {
                $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field'.$systemColumn['field']];
            }

            foreach ($dataCollector->getHeaderFields() as $fieldId => $label) {
                $headerFields[] = $label;
            }

            return $headerFields;
        }

        // Config: tokens
        if ('tokens' === $config->export) {
            foreach ($config->tokenFields as $column) {
                $headerFields[] = $column['headerField'];
            }

            return $headerFields;
        }

        // Config: fields
        // We do this here so we don't have to do it in the loop
        $dataHeaderFields = $dataCollector->getHeaderFields();

        foreach ($config->fields as $column) {
            if ('' !== $column['name']) {
                $headerFields[] = $column['name'];
            } else {
                // System column
                if (array_key_exists($column['field'], $systemColumns)) {
                    $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field'.$column['field']];
                } else {
                    if (isset($dataHeaderFields[$column['field']])) {
                        $headerFields[] = $dataHeaderFields[$column['field']];
                    } else {
                        $headerFields[] = '';
                    }
                }
            }
        }

        return $headerFields;
    }

    /**
     * Prepares the default export configuration according to the configuration.
     *
     * @param \Database\Result|object $config
     *
     * @return array
     */
    protected function prepareDefaultExportConfig($config, DataCollector $dataCollector)
    {
        $columnConfig = [];

        // Config: all
        if ('all' === $config->export) {
            // Add base information columns (system columns)
            foreach (Lead::getSystemColumns() as $systemColumn) {
                $columnConfig[] = $systemColumn;
            }

            // Add export data column config.
            foreach ($dataCollector->getFieldsData() as $fieldId => $fieldConfig) {
                $fieldConfig = $this->handleContaoSpecificConfig($fieldConfig);

                $fieldConfig['value'] = 'all';
                $columnConfig[] = $fieldConfig;
            }

            return $columnConfig;
        }

        // We do this here so we don't have to do it in the loop
        $fieldsData = $dataCollector->getFieldsData();

        // Config: tokens
        if ('tokens' === $config->export) {
            $allFieldsConfig = [];

            foreach ($fieldsData as $fieldConfig) {
                $allFieldsConfig[] = $this->handleContaoSpecificConfig($fieldConfig);
            }

            foreach ($config->tokenFields as $column) {
                $column = $column['allFieldsConfig'] = $allFieldsConfig;

                $columnConfig[] = $column;
            }

            return $columnConfig;
        }

        // Config: custom
        $systemColumns = Lead::getSystemColumns();

        foreach ($config->fields as $column) {
            // System column
            if (array_key_exists($column['field'], $systemColumns)) {
                $columnConfig[] = $systemColumns[$column['field']];
            } else {
                // Skip non existing fields
                if (!isset($fieldsData[$column['field']])) {
                    continue;
                }

                // Merge form field config with custom export config
                $fieldConfig = array_merge(
                    $fieldsData[$column['field']],
                    $column
                );

                $fieldConfig = $this->handleContaoSpecificConfig($fieldConfig);

                $columnConfig[] = $fieldConfig;
            }
        }

        return $columnConfig;
    }

    /**
     * Handles some Contao specific configurations.
     *
     * @return array
     */
    protected function handleContaoSpecificConfig(array $fieldConfig)
    {
        // Yes and No transformer for checkboxes with only one option
        if ($fieldConfig['label'] === $fieldConfig['name']
            && 'checkbox' === $fieldConfig['type']
            && '' !== $fieldConfig['options']
        ) {
            $options = deserialize($fieldConfig['options'], true);

            if (1 === \count($options)) {
                $fieldConfig['transformers'] = array_merge(
                    (array) $fieldConfig['transformers'],
                    ['yesno']
                );
            }
        }

        return $fieldConfig;
    }

    /**
     * Update last export date.
     *
     * @param \stdClass $config
     */
    protected function updateLastRun($config): void
    {
        if (null !== $this->newLastRun) {
            \Database::getInstance()
               ->prepare('UPDATE tl_lead_export SET lastRun=? WHERE id=?')
               ->execute($this->newLastRun, $config->id)
            ;
        }
    }

    /**
     * Checks if the result of the export is false (which means an error occured)
     * or if it equals 0 (which means no rows are to be exported).
     *
     * @param int|false $result
     *
     * @throws ExportFailedException
     */
    protected function handleDefaultExportResult($result): void
    {
        // General error
        if (false === $result) {
            throw new ExportFailedException($GLOBALS['TL_LANG']['tl_lead_export']['exportError']['general']);
        }

        // No rows
        if (0 === $result) {
            throw new ExportFailedException($GLOBALS['TL_LANG']['tl_lead_export']['exportError']['noRows']);
        }
    }
}
