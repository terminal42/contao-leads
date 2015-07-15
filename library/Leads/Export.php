<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Leads;

use Haste\IO\Reader\ArrayReader;
use Leads\DataTransformer\DataTransformerInterface;
use Leads\Exporter\Csv;
use Leads\Exporter\Utils\File;
use Leads\Exporter\Xls;
use Leads\Exporter\Xlsx;

/**
 * Class Export
 * @deprecated This class will be removed in 2.0.
 */
class Export
{
    /**
     * Export data to CSV
     * @param object
     * @param array
     * @deprecated Use the Csv class instead. To be removed in 2.0.
     */
    public function exportCsv($objConfig, $arrIds=null)
    {
        $csvExporter = new Csv();
        $csvExporter->export($objConfig, $arrIds);
    }

    /**
     * Export data to XLS
     * @param object
     * @deprecated Use the Xls class instead. To be removed in 2.0.
     */
    public function exportXls($objConfig, $arrIds=null)
    {
        $xlsExporter = new Xls();
        $xlsExporter->export($objConfig, $arrIds);
    }

    /**
     * Export data to XLSX
     * @param object
     * @deprecated Use the Xlsx class instead. To be removed in 2.0.
     */
    public function exportXlsx($objConfig, $arrIds=null)
    {
        $xlsxExporter = new Xlsx();
        $xlsxExporter->export($objConfig, $arrIds);
    }

    /**
     * Get the filename from config
     * @param object
     * @return string
     * @deprecated Use File::getName() instead. To be removed in 2.0.
     */
    public function getFilename($objConfig)
    {
        return File::getName($objConfig);
    }

    /**
     * Generate the export row
     * @param array
     * @param object
     * @return array
     * @deprecated Use the Row class instead. To be removed in 2.0.
     */
    public static function generateExportRow($arrData, $objConfig)
    {
        $arrRow = array();
        $arrFirst = reset($arrData);
        $arrFields = array();

        // Add base information columns
        if ($objConfig->export == 'all') {
            $arrFields[] = array
            (
                'field' => '_form',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
                'value' => 'all',
                'format' => 'raw'
            );

            $arrFields[] = array
            (
                'field' => '_created',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
                'value' => 'all',
                'format' => 'datim'
            );

            $arrFields[] = array
            (
                'field' => '_member',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
                'value' => 'all',
                'format' => 'raw'
            );
        } else {

            foreach (array('_form', '_created', '_member', '_skip') as $specialField) {
                if ($objConfig->fields[$specialField]) {
                    $arrFields[] = $objConfig->fields[$specialField];
                }
            }
        }

        $arrFields = array_merge($arrFields, static::$arrFields);

        foreach ($arrFields as $arrField) {

            // Add custom logic
            if (isset($GLOBALS['TL_HOOKS']['getLeadsExportRow']) && is_array($GLOBALS['TL_HOOKS']['getLeadsExportRow'])) {
                $varValue = null;

                foreach ($GLOBALS['TL_HOOKS']['getLeadsExportRow'] as $callback) {
                    if (is_array($callback)) {
                        $varValue = \System::importStatic($callback[0])->$callback[1]($arrField, $arrData, $objConfig, $varValue);
                    } elseif (is_callable($callback)) {
                        $varValue = $callback($arrField, $arrData, $objConfig, $varValue);
                    }
                }

                // Store the value
                if ($varValue !== null) {
                    $arrRow[] = $varValue;
                    continue;
                }
            }

            // Show yes/no for single checkbox value
            if ($arrField['label'] == $arrField['name'] && $arrField['type'] == 'checkbox' && $arrField['options'] != '') {
                $arrOptions = deserialize($arrField['options']);

                if (is_array($arrOptions) && count($arrOptions) == 1) {
                    if ($arrData[$arrField['id']]['value'] == '') {
                        $arrRow[] = $GLOBALS['TL_LANG']['MSC']['no'];
                        continue;
                    } elseif ($arrData[$arrField['id']]['value'] == '1') {
                        $arrRow[] = $GLOBALS['TL_LANG']['MSC']['yes'];
                        continue;
                    }
                }
            }

            $varValue = '';
            $strLabel = '';
            $strFormat = $objConfig->fields[$arrField['id']]['format'];

            // Get the special field value and label
            if (isset($arrField['field'])) {
                switch ($arrField['field']) {
                    case '_form':
                        $varValue = $arrFirst['form_id'];
                        $strLabel = $arrFirst['form_name'];
                        break;

                    case '_created':
                        $varValue = $arrFirst['created'];
                        break;

                    case '_member':
                        $varValue = $arrFirst['member_id'];
                        $strLabel = $arrFirst['member_name'];
                        break;

                    case '_skip':
                        $varValue = null;
                        $strLabel = null;
                        break;
                }

                $strFormat = $arrField['format'];
            } else {
                $varValue = implode(', ', deserialize($arrData[$arrField['id']]['value'], true));

                // Prepare the label
                if ($arrData[$arrField['id']]['label'] != '') {
                    $strLabel = $arrData[$arrField['id']]['label'];
                    $arrLabel = deserialize($arrData[$arrField['id']]['label']);

                    if (is_array($arrLabel) && !empty($arrLabel)) {
                        $strLabel = implode(', ', $arrLabel);
                    }
                }
            }

            /**
             * Apply special formatting
             * @var $dataTransformer DataTransformerInterface
             */
            if (in_array($strFormat, array_keys($GLOBALS['LEADS_DATA_TRANSFORMERS']))) {

                $dataTransformer = new $GLOBALS['LEADS_DATA_TRANSFORMERS'][$strFormat]();

                $arrRow[] = $dataTransformer->transform($varValue);

                continue;
            }

            // Fallback formatting if no data transformer specified
            switch ($objConfig->fields[$arrField['id']]['value']) {
                case 'value':
                    $arrRow[] = $varValue;
                    break;

                case 'label':
                    $arrRow[] = $strLabel ? $strLabel : $varValue;
                    break;

                default:
                    if ($strLabel === '' && $varValue === '') {
                        $arrRow[] = ''; // No label, no value
                    } elseif ($strLabel === '' && $varValue !== '') {
                        $arrRow[] = $varValue; // No label, but value
                    } elseif ($strLabel !== '' && $varValue === '') {
                        $arrRow[] = $strLabel; // Label, no value
                    } elseif ($strLabel == $varValue) {
                        $arrRow[] = $varValue; // Label the same as value
                    } else {
                        $arrRow[] = $strLabel . ' [' . $varValue . ']'; // Different label and value
                    }
                    break;
            }
        }
        
        return $arrRow;
    }

    /**
     * Get the export fields
     *
     * @param object
     * @param array
     * @return ArrayReader
     *
     * @deprecated Use the DataCollector class instead.
     */
    protected function getExportData($objConfig, $arrIds=null)
    {
        $dataCollector = new DataCollector($objConfig->master);

        // Limit the fields
        if ($objConfig->export != 'all') {
            $arrLimitFields = array_keys($objConfig->fields);
            $dataCollector->setFieldIds(array_map('intval', $arrLimitFields));
        }

        if (null !== $arrIds) {
            $dataCollector->setLeadDataIds($arrIds);
        }

        $objReader = new ArrayReader($dataCollector->getExportData());

        // Add header fields
        if ($objConfig->headerFields) {
            $arrHeader = array();

            // Add base information columns
            if ($objConfig->export == 'all') {
                \System::loadLanguageFile('tl_lead_export');

                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead_export']['field_form'];
                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead_export']['field_created'];
                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead_export']['field_member'];
            } else {
                if ($objConfig->fields['_form']) {
                    $arrHeader[] = $objConfig->fields['_form']['name'];
                }
                if ($objConfig->fields['_created']) {
                    $arrHeader[] = $objConfig->fields['_created']['name'];
                }
                if ($objConfig->fields['_member']) {
                    $arrHeader[] = $objConfig->fields['_member']['name'];
                }
            }

            // Add export data header fields
            foreach ($dataCollector->getHeaderFields() as $fieldId => $label) {

                // Use a custom header field
                if ($objConfig->fields[$fieldId]['name'] != '') {
                    $arrHeader[] = $objConfig->fields[$fieldId]['name'];
                } else {
                    $arrHeader[] = $label;
                }
            }

            $objReader->setHeaderFields($arrHeader);
        }

        return $objReader;
    }
}
