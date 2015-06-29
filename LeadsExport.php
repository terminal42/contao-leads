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

        $objWriter = new CsvFileWriter($this->getFilename($objConfig));

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        if (!$objWriter->writeFrom($objReader)) {
            $objResponse = new \Haste\Http\Response\Response('Data export failed.', 500);
            $objResponse->send();
        }

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

        $objWriter = new ExcelFileWriter($this->getFilename($objConfig));
        $objWriter->setFormat('Excel5');

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        if (!$objWriter->writeFrom($objReader)) {
            $objResponse = new \Haste\Http\Response\Response('Data export failed.', 500);
            $objResponse->send();
        }

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

        $objWriter = new ExcelFileWriter($this->getFilename($objConfig));
        $objWriter->setFormat('Excel2007');

        // Add header fields
        if ($objConfig->headerFields) {
            $objWriter->enableHeaderFields();
        }

        $objWriter->setRowCallback(function($arrData) use ($objConfig) {
            return static::generateExportRow($arrData, $objConfig);
        });

        if (!$objWriter->writeFrom($objReader)) {
            $objResponse = new \Haste\Http\Response\Response('Data export failed.', 500);
            $objResponse->send();
        }

        $objFile = new \File($objWriter->getFilename());
        $objFile->sendToBrowser();
    }

    /**
     * Get the filename from config
     * @param object
     * @return string
     */
    protected function getFilename($objConfig)
    {
        if ($objConfig->filename == '') {
            return '';
        }

        $arrTokens = array
        (
            'time' => \Date::parse($GLOBALS['TL_CONFIG']['timeFormat']),
            'date' => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat']),
            'datim' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat']),
        );

        // Add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens']) && is_array($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens'])) {
            foreach ($GLOBALS['TL_HOOKS']['getLeadsFilenameTokens'] as $callback) {
                if (is_array($callback)) {
                    $arrTokens = \System::importStatic($callback[0])->$callback[1]($arrTokens, $objConfig);
                } elseif (is_callable($callback)) {
                    $arrTokens = $callback($arrTokens, $objConfig);
                }
            }
        }

        return \String::parseSimpleTokens($objConfig->filename, $arrTokens);
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
            if ($objConfig->fields['_form']) {
                $arrFields[] = $objConfig->fields['_form'];
            }
            if ($objConfig->fields['_created']) {
                $arrFields[] = $objConfig->fields['_created'];
            }
            if ($objConfig->fields['_member']) {
                $arrFields[] = $objConfig->fields['_member'];
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

            // Apply special formatting
            switch ($strFormat) {
                case 'date':
                case 'datim':
                case 'time':
                    $arrRow[] = \Date::parse($GLOBALS['TL_CONFIG'][$strFormat . 'Format'], $varValue);
                    continue 2; break;
            }

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
     * @param object
     * @param array
     * @return object|null
     */
    protected function getExportData($objConfig, $arrIds=null)
    {
        $arrLimitFields = array();

        // Limit the fields
        if ($objConfig->export != 'all') {
            $arrLimitFields = array_keys($objConfig->fields);
            $arrLimitFields = array_map('intval', $arrLimitFields);
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
            ORDER BY " . (!empty($arrLimitFields) ? \Database::getInstance()->findInSet("ld.field_id", $arrLimitFields) : "sorting")
        )->executeUncached($objConfig->master);

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
                l.form_id AS form_id,
                (SELECT title FROM tl_form WHERE id=l.form_id) AS form_name,
                l.member_id AS member_id,
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
