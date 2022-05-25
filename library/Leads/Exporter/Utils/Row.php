<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */


namespace Leads\Exporter\Utils;
use Leads\DataTransformer\DataTransformerInterface;

/**
 * Class Row
 *
 * Represents a row in any export format (whether you use it in csv, excel, xml etc.).
 *
 * @package Leads\Exporter\Utils
 */
class Row
{
    /**
     * Config.
     * @var \Database\Result
     */
    private $config;

    /**
     * Column configuration.
     * An array in the following format.
     *
     * array (
     *     array (
     *         'id'             => <id>, // tl_form_field.id
     *         'field'          => <custom name>, // custom name (for system columns)
     *         'name'           => 'name', // form field name
     *         'label'          => 'Label',  // Used for the column header
     *         'inputType'      => 'checkbox',
     *         'options'        => array(),
     *         'value'          => 'all',   // One of "all", "label" or "value" (defines if only value, only label or both should be exported)
     *         'transformers'   => array('datim') // See $GLOBALS['LEADS_DATA_TRANSFORMERS']
     *     )
     * )
     *
     * @var array
     */
    private $columnConfig = array();


    /**
     * Constructor.
     *
     * @param \Database\Result $config
     * @param array            $columnConfig
     */
    function __construct($config, array $columnConfig)
    {
        $this->config = $config;
        $this->columnConfig = $columnConfig;
    }

    /**
     * Returns the prepared row according to the Row instance configuration.
     *
     * @param array $data
     *
     * @return array
     */
    public function compile(array $data)
    {
        $compiled = array();

        foreach ($this->columnConfig as $columnConfig) {

            // Add custom logic
            if (isset($GLOBALS['TL_HOOKS']['getLeadsExportRow'])
                && is_array($GLOBALS['TL_HOOKS']['getLeadsExportRow'])
            ) {
                $value = null;

                foreach ($GLOBALS['TL_HOOKS']['getLeadsExportRow'] as $callback) {
                    if (is_array($callback)) {
                        $value = \System::importStatic($callback[0])->{$callback[1]}($columnConfig, $data, $this->config, $value);
                    } elseif (is_callable($callback)) {
                        $value = $callback($columnConfig, $data, $this->config, $value);
                    }
                }

                // Store the value
                if ($value !== null) {
                    $compiled[] = $value;
                    continue;
                }
            }

            // Regular form field
            if (isset($columnConfig['id'])) {
                $value = isset($columnConfig['id'], $data[$columnConfig['id']]['value']) ? $data[$columnConfig['id']]['value'] : null;
                $label = $columnConfig['label'];
            } else {
                // Internal field
                $row   = current($data);
                $value = isset($columnConfig['valueColRef'], $row[$columnConfig['valueColRef']]) ? $row[$columnConfig['valueColRef']] : null;
                $label = isset($columnConfig['labelColRef'], $row[$columnConfig['labelColRef']]) ? $row[$columnConfig['labelColRef']] : null;
            }

            $value      = static::transformValue($value, $columnConfig);
            $label      = static::prepareLabel($label);
            $compiled[] = static::getValueForOutput($columnConfig['value'], $value, $label);

        }

        return $compiled;
    }

    /**
     * Transform the value to a desired format.
     *
     * @param               $value
     * @param array         $columnConfig
     *
     * @return string
     */
    public static function transformValue($value, array $columnConfig)
    {
        $value = implode(', ', deserialize($value, true));

        // Merge transformers chosen by user (format) with an array of arbitrary ones
        // defined by any developer.
        if ($columnConfig['format'] ?? false) {
            $columnConfig['transformers'] = array_merge(
                (array) ($columnConfig['format'] ?? []),
                (array) ($columnConfig['transformers'] ?? [])
            );
        }

        /**
         * Apply data transformers
         * @var $dataTransformer DataTransformerInterface
         */
        $transformers = (array) ($columnConfig['transformers'] ?? []);

        foreach ($transformers as $transformerKey) {

            if (in_array($transformerKey, array_keys($GLOBALS['LEADS_DATA_TRANSFORMERS']))) {

                $dataTransformer = new $GLOBALS['LEADS_DATA_TRANSFORMERS'][$transformerKey]();

                $value = $dataTransformer->transform($value);
            }
        }

        return (string) $value;
    }

    /**
     * Formats the value according a given output format.
     *
     * @param string    $outputFormat
     * @param  mixed    $value
     * @param null      $label
     *
     * @return string
     */
    public static function getValueForOutput($outputFormat, $value, $label = null)
    {
        if ($outputFormat === 'value') {

            return $value;
        }

        if ($outputFormat === 'label') {

            return $label ? $label : $value;
        }

        if ($label === '' && $value === '') {

            return ''; // No label, no value
        } elseif ($label === '' && $value !== '') {

            return $value; // No label, but value
        } elseif ($label !== '' && $value === '') {

            return $label; // Label, no value
        } elseif ($label == $value) {

            return $value; // Label the same as value
        } else {

            return $label . ' [' . $value . ']'; // Different label and value
        }
    }

    /**
     * Prepares the label. Can handle both, a regular and a serialized string.
     *
     * @param  string $label
     * @return string
     */
    public static function prepareLabel($label)
    {
        if ($label != '') {
            $labelChunks = deserialize($label);

            if (is_array($labelChunks) && !empty($labelChunks)) {
                $label = implode(', ', $label);
            }
        }

        return $label;
    }
}
