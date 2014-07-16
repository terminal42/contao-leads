<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

use \Haste\IO\Reader\ArrayReader;
use \Haste\IO\Writer\CsvFileWriter;
use \Haste\IO\Writer\ExcelFileWriter;

class LeadsExport
{

    /**
     * Fields data
     * @var array
     */
    protected static $arrFields = array();

    /**
     * Export data to CSV
     * @param object
     * @param array
     */
    public function exportCsv($objConfig, $arrIds=null)
    {
        $objReader = $this->getExportData($objConfig, $arrIds);

        $objWriter = new CsvFileWriter();

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        $objWriter->writeFrom($objReader);

        $objFile = new \File($objWriter->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Export data to XLS
     * @param object
     * @param array
     */
    public function exportXls($objConfig, $arrIds=null)
    {
        $objReader = $this->getExportData($objConfig, $arrIds);

        $objWriter = new ExcelFileWriter();
        $objWriter->setFormat('Excel5');

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        $objWriter->writeFrom($objReader);

        $objFile = new \File($objWriter->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Export data to XLSX
     * @param object
     * @param array
     */
    public function exportXlsx($objConfig, $arrIds=null)
    {
        $objReader = $this->getExportData($objConfig, $arrIds);

        $objWriter = new ExcelFileWriter();
        $objWriter->setFormat('Excel2007');

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        $objWriter->writeFrom($objReader);

        $objFile = new \File($objWriter->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Generate the export row
     * @param array
     * @param object
     * @return array
     */
    protected static function generateExportRow($arrData, $objConfig)
    {
        $arrRow = array();
        $arrFirst = reset($arrData);

        // Add base information columns
        if ($objConfig->includeFormId) {
            $arrRow[] = $arrFirst['form_name'];
        }
        if ($objConfig->includeCreated) {
            $arrRow[] = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $arrFirst['created']);
        }
        if ($objConfig->includeMember) {
            $arrRow[] = $arrFirst['member_name'];
        }

        foreach (static::$arrFields as $arrField) {

            // Add custom logic
            if (isset($GLOBALS['TL_HOOKS']['getLeadsExportRow']) && is_array($GLOBALS['TL_HOOKS']['getLeadsExportRow'])) {
                $varValue = false;

                foreach ($GLOBALS['TL_HOOKS']['getLeadsExportRow'] as $callback) {
                    if (is_array($callback)) {
                        $varValue = \System::importStatic($callback[0])->$callback[1]($arrField, $arrData, $objConfig, $varValue);
                    } elseif (is_callable($callback)) {
                        $varValue = $callback($arrField, $arrData, $objConfig, $varValue);
                    }
                }

                // Store the value
                if ($varValue !== false) {
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

            $strFormat = $objConfig->fields[$arrField['id']]['format'];

            // Apply special formatting
            switch ($strFormat) {
                case 'date':
                case 'datim':
                case 'time':
                    $arrRow[] = \Date::parse($GLOBALS['TL_CONFIG'][$strFormat . 'Format'], $arrData[$arrField['id']]);
                    continue 2; break;
            }

            $varValue = implode(', ', deserialize($arrData[$arrField['id']]['value'], true));
            $strLabel = '';

            // Prepare the label
            if ($arrData[$arrField['id']]['label'] != '') {
                $strLabel = $arrData[$arrField['id']]['label'];
                $arrLabel = deserialize($arrData[$arrField['id']]['label']);

                if (is_array($arrLabel) && !empty($arrLabel)) {
                    $strLabel = implode(', ', $arrLabel);
                }
            }

            switch ($objConfig->fields[$arrField['id']]['value']) {
                case 'value':
                    $arrRow[] = $varValue;
                    break;

                case 'label':
                    $arrRow[] = $strLabel;
                    break;

                default:
                    $arrRow[] = $strLabel . ' [' . $varValue . ']';
                    break;
            }
        }

        return $arrRow;
    }

    /**
     * Get the export fields
     * @param object
     * @param array
     * @return object|null
     */
    protected function getExportData($objConfig, $arrIds=null)
    {
        $arrLimitFields = array();

        // Limit the fields
        if ($objConfig->limitFields) {
            $arrLimitFields = array_keys($objConfig->fields);
        }

        $objFields = \Database::getInstance()->prepare("
            SELECT * FROM (
                SELECT
                    ld.master_id AS id,
                    IFNULL(ff.name, ld.name) AS name,
                    IF(ff.label IS NULL OR ff.label='', ld.name, ff.label) AS label,
                    ff.type,
                    ff.options,
                    ld.field_id,
                    ld.sorting
                FROM tl_lead_data ld
                LEFT JOIN tl_form_field ff ON ff.id=ld.master_id
                LEFT JOIN tl_lead l ON ld.pid=l.id
                WHERE l.master_id=?" . (!empty($arrLimitFields) ? (" AND ld.field_id IN (" . implode(',', $arrLimitFields) . ")") : "") . "
                ORDER BY l.master_id!=l.form_id
            ) ld
            GROUP BY field_id
            ORDER BY sorting
        ")->executeUncached($objConfig->master);

        static::$arrFields = array();

        // Collect fields data
        while ($objFields->next()) {
            static::$arrFields[$objFields->id] = $objFields->row();
        }

        $arrData = array();
        $objData = \Database::getInstance()->prepare("
            SELECT
                ld.*,
                l.created,
                (SELECT title FROM tl_form WHERE id=l.form_id) AS form_name,
                IFNULL((SELECT CONCAT(firstname, ' ', lastname) FROM tl_member WHERE id=l.member_id), '') AS member_name
            FROM tl_lead_data ld
            LEFT JOIN tl_lead l ON l.id=ld.pid
            WHERE l.master_id=?" . ((is_array($arrIds) && !empty($arrIds)) ? (" AND l.id IN(" . implode(',', $arrIds) . ")") : "") . "
            ORDER BY l.created DESC
        ")->execute($objConfig->master);

        while ($objData->next()) {
            $arrData[$objData->pid][$objData->field_id] = $objData->row();
        }

        $objReader = new ArrayReader($arrData);

        // Add header fields
        if ($objConfig->headerFields) {
            $arrHeader = array();

            // Add base information columns
            if ($objConfig->includeFormId) {
                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead']['form_id'][0];
            }
            if ($objConfig->includeCreated) {
                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead']['created'][0];
            }
            if ($objConfig->includeMember) {
                $arrHeader[] = $GLOBALS['TL_LANG']['tl_lead']['member'][0];
            }

            $objFields->reset();

            while ($objFields->next()) {

                // Use a custom header field
                if ($objConfig->fields[$objFields->id]['name'] != '') {
                    $arrHeader[] = $objConfig->fields[$objFields->id]['name'];
                    continue;
                }

                // Show single checkbox label as field label
                if ($objFields->label == $objFields->name && $objFields->type == 'checkbox' && $objFields->options != '') {
                    $arrOptions = deserialize($objFields->options);

                    if (is_array($arrOptions) && count($arrOptions) == 1) {
                        $arrHeader[] = $arrOptions[0]['label'];
                        continue;
                    }
                }

                $arrHeader[] = $objFields->label;
            }

            $objReader->setHeaderFields($arrHeader);
        }

        return $objReader;
    }
}
