<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */
namespace Terminal42\LeadsBundle\Exporter;


use Terminal42\LeadsBundle\DataCollector;
use Terminal42\LeadsBundle\Leads;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * Returns true if available.
     *
     * @return bool
     */
    abstract public function isAvailable();

    /**
     * Exports a given set of data row ids using a given configuration.
     *
     * @param \Database\Result $config
     * @param array|null       $ids
     */
    abstract public function export($config, $ids = null);

    /**
     * Prepares the default DataCollector instance based on the configuration.
     *
     * @param \Database\Result $config
     * @param null             $ids
     *
     * @return DataCollector
     */
    protected function prepareDefaultDataCollector($config, $ids = null)
    {
        $dataCollector = new DataCollector($config->master);

        // Limit the fields
        if ($config->export == 'fields') {

            $limitFields = array();
            foreach ($config->fields as $fieldsConfig) {
                $limitFields[] = $fieldsConfig['field'];
            }

            $dataCollector->setFieldIds($limitFields);
        }

        if (null !== $ids) {
            $dataCollector->setLeadDataIds($ids);
        }

        return $dataCollector;
    }

    /**
     * Prepares the header fields according to the configuration.
     *
     * @param \Database\Result $config
     * @param DataCollector    $dataCollector
     *
     * @return array
     */
    protected function prepareDefaultHeaderFields($config, DataCollector $dataCollector)
    {
        $headerFields = array();

        // Config: all
        if ($config->export == 'all') {
            foreach (Leads::getSystemColumns() as $systemColumn) {
                $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field' . $systemColumn['field']];
            }

            foreach ($dataCollector->getHeaderFields() as $fieldId => $label) {

                $headerFields[] = $label;
            }

            return $headerFields;
        }

        // Config: tokens
        if ($config->export == 'tokens') {

            foreach ($config->tokenFields as $column) {

                $headerFields[] = $column['headerField'];
            }

            return $headerFields;
        }

        // Config: fields
        // We do this here so we don't have to do it in the loop
        $dataHeaderFields = $dataCollector->getHeaderFields();

        foreach ($config->fields as $column) {
            if ($column['name'] != '') {

                $headerFields[] = $column['name'];
            } else {
                // System column
                if (in_array($column['field'], array_keys(Leads::getSystemColumns()))) {
                    $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field' . $column['field']];
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
     * @param \Database\Result $config
     * @param DataCollector    $dataCollector
     * @return array
     */
    protected function prepareDefaultExportConfig($config, DataCollector $dataCollector)
    {
        $columnConfig = array();

        // Config: all
        if ($config->export == 'all') {
            // Add base information columns (system columns)
            foreach (Leads::getSystemColumns() as $systemColumn) {
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
        if ($config->export == 'tokens') {

            $allFieldsConfig = array();
            foreach ($fieldsData as $fieldConfig) {
                $allFieldsConfig[] = $this->handleContaoSpecificConfig($fieldConfig);
            }


            foreach ($config->tokenFields as $column) {

                $column = array_merge($column, array(
                    'allFieldsConfig' => $allFieldsConfig
                ));

                $columnConfig[] = $column;
            }

            return $columnConfig;
        }

        // Config: custom
        $systemColumns =  Leads::getSystemColumns();

        foreach ($config->fields as $column) {

            // System column
            if (in_array($column['field'], array_keys($systemColumns))) {

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
     * @param array     $fieldConfig
     * @return array
     */
    protected function handleContaoSpecificConfig(array $fieldConfig)
    {
        // Yes and No transformer for checkboxes with only one option
        if ($fieldConfig['label'] == $fieldConfig['name']
            && $fieldConfig['type'] == 'checkbox'
            && $fieldConfig['options'] != ''
        ) {
            $options = deserialize($fieldConfig['options'], true);

            if (count($options) == 1) {
                $fieldConfig['transformers'] = array_merge(
                    (array) $fieldConfig['transformers'],
                    array('yesno')
                );
            }
        }

        return $fieldConfig;
    }
}
