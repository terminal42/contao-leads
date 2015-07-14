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
            $dataCollector->setFieldIds(array_map('intval', array_keys($config->fields)));
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
    protected function prepareHeaderFields(\Database_Result $config, DataCollector $dataCollector)
    {
        $headerFields = array();

        // Add base information columns (system columns)
        foreach (array('_form', '_created', '_member') as $systemColumn) {
            if ($config->export == 'all') {
                // Will not get loaded multiple times
                \System::loadLanguageFile('tl_lead_export');

                $headerFields[] = $GLOBALS['TL_LANG']['tl_lead_export']['field' . $systemColumn];
            } else {
                if ($config->fields[$systemColumn]) {
                    $headerFields[] = $config->fields[$systemColumn]['name'];
                }
            }
        }

        // Add export data header fields
        foreach ($dataCollector->getHeaderFields() as $fieldId => $label) {

            // Use a custom header field
            if ($config->fields[$fieldId]['name'] != '') {
                $headerFields[] = $config->fields[$fieldId]['name'];
            } else {
                $headerFields[] = $label;
            }
        }

        return $headerFields;
    }
}