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

class DataCollector
{
    /**
     * Collects fields data for given config
     *
     * @param \Database_Result $config
     *
     * @return array
     */
    public static function collectFieldsData($config)
    {
        $fields = array();
        $arrLimitFields = array();

        // Limit the fields
        if ($config->export != 'all') {
            $arrLimitFields = array_keys($config->fields);
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
        )->execute($config->master);

        // Collect fields data
        while ($objFields->next()) {
            $fields[$objFields->id] = $objFields->row();
        }

        return $fields;
    }

    /**
     * Get the export fields
     * @param object
     * @param array
     * @return object|null
     */
    public static function fetchExportData($objConfig, $arrIds = null)
    {
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

            $fields = static::collectFieldsData($objConfig);

            foreach ($fields as $fieldId => $row) {

                // Use a custom header field
                if ($objConfig->fields[$fieldId]['name'] != '') {
                    $arrHeader[] = $objConfig->fields[$fieldId]['name'];
                    continue;
                }

                // Show single checkbox label as field label
                if ($row['label'] == $row['name'] && $row['type'] == 'checkbox' && $row['options'] != '') {
                    $arrOptions = deserialize($row['options']);

                    if (is_array($arrOptions) && count($arrOptions) == 1) {
                        $arrHeader[] = $arrOptions[0]['label'];
                        continue;
                    }
                }

                $arrHeader[] = $row['label'];
            }

            $objReader->setHeaderFields($arrHeader);
        }

        return $objReader;
    }
}