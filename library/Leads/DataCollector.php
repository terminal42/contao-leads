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

class DataCollector
{
    /**
     * Form ID
     * @var int
     */
    private $formId;

    /**
     * Form field ids limitation
     * @var array
     */
    private $fieldIds = array();

    /**
     * Lead data row ids limitation
     * @var array
     */
    private $leadDataIds = array();

    /**
     * Cache for getFieldsData()
     * @var array
     */
    private $getFieldsDataCache = array();

    /**
     * Constructor.
     *
     * @param int $formId
     */
    public function __construct($formId)
    {
        $this->formId = (int) $formId;
    }

    /**
     * Returns the form id.
     *
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Get the array of field ids.
     *
     * @return array
     */
    public function getFieldIds()
    {
        return $this->fieldIds;
    }

    /**
     * Limit the result to a given array of field ids.
     *
     * @param array $fieldIds
     */
    public function setFieldIds(array $fieldIds)
    {
        $this->fieldIds = array_map('intval', $fieldIds);
    }

    /**
     * Get the array of lead data ids.
     *
     * @return array
     */
    public function getLeadDataIds()
    {
        return $this->leadDataIds;
    }

    /**
     * Limit the export result to a given array of lead data ids.
     *
     * @param array $leadDataIds
     */
    public function setLeadDataIds(array $leadDataIds)
    {
        $this->leadDataIds = $leadDataIds;
    }

    /**
     * Fetches the form field data. Use setFieldIds() if you want to limit the
     * result to a given array of form field ids.
     *
     * @return array
     */
    public function getFieldsData()
    {
        $cacheKey = md5($this->formId . ':' . implode(',', $this->fieldIds));

        if (isset($this->getFieldsDataCache[$cacheKey])) {

            return $this->getFieldsDataCache[$cacheKey];
        }

        $data = array();
        $db = \Database::getInstance()->prepare("
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
                WHERE l.master_id=?" . (!empty($this->fieldIds) ? (" AND ld.field_id IN (" . implode(',', $this->fieldIds) . ")") : "") . "
                ORDER BY l.master_id!=l.form_id
            ) ld
            GROUP BY field_id
            ORDER BY " . (!empty($this->fieldIds) ? \Database::getInstance()->findInSet("ld.field_id", $this->fieldIds) : "sorting")
        )->execute($this->formId);

        while ($db->next()) {
            $data[$db->id] = $db->row();
        }

        $this->getFieldsDataCache[$cacheKey] = $data;

        return $data;
    }

    /**
     * Fetches the export (tl_lead_data) data. Use setLeadDataIds() if you want to limit the
     * result to a given array of tl_lead_data ids.
     *
     * @return array
     */
    public function getExportData()
    {
        $data = array();
        $db = \Database::getInstance()->prepare("
            SELECT
                ld.*,
                l.created,
                l.form_id AS form_id,
                (SELECT title FROM tl_form WHERE id=l.form_id) AS form_name,
                l.member_id AS member_id,
                IFNULL((SELECT CONCAT(firstname, ' ', lastname) FROM tl_member WHERE id=l.member_id), '') AS member_name
            FROM tl_lead_data ld
            LEFT JOIN tl_lead l ON l.id=ld.pid
            WHERE l.master_id=?" . ((!empty($this->leadDataIds)) ? (" AND l.id IN(" . implode(',', $this->leadDataIds) . ")") : "") . "
            ORDER BY l.created DESC
        ")->execute($this->formId);

        while ($db->next()) {
            $data[$db->pid][$db->field_id] = $db->row();
        }

        return $data;
    }

    /**
     * Get the header fields of the fields data.
     *
     * @return array
     */
    public function getHeaderFields()
    {
        $headerFields = array();

        foreach ($this->getFieldsData() as $fieldId => $row) {

            // Show single checkbox label as field label
            if ($row['label'] == $row['name']
                && $row['type'] == 'checkbox'
                && $row['options'] != ''
            ) {
                $options = deserialize($row['options'], true);

                if (count($options) == 1) {
                    $headerFields[$fieldId] = $options[0]['label'];
                    continue;
                }
            }

            $headerFields[$fieldId] = $row['label'];
        }

        return $headerFields;
    }
}