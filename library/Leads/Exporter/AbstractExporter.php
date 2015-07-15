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


use Leads\DataCollector;

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
     * @param \Database_Result $config
     * @param array|null       $ids
     */
    abstract public function export(\Database_Result $config, $ids = null);

    /**
     * Prepares the default DataCollector instance based on the configuration.
     *
     * @param \Database_Result $config
     * @param null             $ids
     *
     * @return DataCollector
     */
    protected function prepareDefaultDataCollector(\Database_Result $config, $ids = null)
    {
        $dataCollector = new DataCollector($config->master);

        // Limit the fields
        if ($config->export != 'all') {

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
     * @param \Database_Result $config
     * @param DataCollector    $dataCollector
     *
     * @return array
     */
    protected function prepareDefaultHeaderFields(\Database_Result $config, DataCollector $dataCollector)
    {
        $headerFields = array();

        if ($config->export == 'all') {
            foreach ($this->getSystemColumns() as $systemColumn) {
                $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field' . $systemColumn['field']];
            }

            foreach ($dataCollector->getHeaderFields() as $fieldId => $label) {

                $headerFields[] = $label;
            }

            return $headerFields;
        }

        // We do this here so we don't have to do it in the loop
        $dataHeaderFields = $dataCollector->getHeaderFields();

        foreach ($config->fields as $column) {
            if ($column['name'] != '') {

                $headerFields[] = $column['name'];
            } else {
                // System column
                if (in_array($column['field'], $this->getSystemColumns())) {
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
     * Default system columns.
     *
     * @return array
     */
    protected function getSystemColumns()
    {
        \System::loadLanguageFile('tl_lead_export');

        return array(
            array(
                'field'     => '_form',
                'name'      => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
                'value'     => 'all',
                'format'    => 'raw'
            ),
            array(
                'field'     => '_created',
                'name'      => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
                'value'     => 'all',
                'format'    => 'datim'
            ),
            array(
                'field'     => '_member',
                'name'      => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
                'value'     => 'all',
                'format'    => 'raw'
            )
        );
    }
}