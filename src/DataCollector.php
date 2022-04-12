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

namespace Terminal42\LeadsBundle;

use Contao\StringUtil;

class DataCollector
{
    /**
     * Form ID.
     *
     * @var int
     */
    private $formId;

    /**
     * Form field ids limitation.
     *
     * @var array
     */
    private $fieldIds = [];

    /**
     * Lead data row ids limitation.
     *
     * @var array
     */
    private $leadDataIds = [];

    /**
     * Export from.
     *
     * @var int|null
     */
    private $from;

    /**
     * Export to.
     *
     * @var int|null
     */
    private $to;

    /**
     * Cache for getFieldsData().
     *
     * @var array
     */
    private $getFieldsDataCache = [];

    /**
     * Cache for getExportData().
     *
     * @var array
     */
    private $getExportDataCache = [];

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
     */
    public function setFieldIds(array $fieldIds): void
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
     */
    public function setLeadDataIds(array $leadDataIds): void
    {
        $this->leadDataIds = $leadDataIds;
    }

    /**
     * A timestamp from when to start fetching data.
     *
     * @return int|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set a timestamp from when to start fechting data.
     *
     * @param int|null $time
     */
    public function setFrom($time): void
    {
        $this->from = $time;
    }

    /**
     * A timestamp to limit the export to.
     *
     * @return int|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set limit export timestamp.
     *
     * @param int|null $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    /**
     * Gets a cache key for the instance of the data collector.
     *
     * @return string
     */
    public function getCacheKey()
    {
        $key = md5($this->formId.':'.implode(',', $this->fieldIds));

        if (null !== $this->getFrom()) {
            $key .= ':'.(int) $this->getFrom();
        }

        if (null !== $this->getTo()) {
            $key .= ':'.(int) $this->getTo();
        }

        return $key;
    }

    /**
     * Fetches the form field data. Use setFieldIds() if you want to limit the
     * result to a given array of form field ids.
     *
     * @return array
     */
    public function getFieldsData()
    {
        $cacheKey = $this->getCacheKey();

        if (array_key_exists($cacheKey, $this->getFieldsDataCache)) {
            return $this->getFieldsDataCache[$cacheKey];
        }

        $where = ['tl_lead.master_id=?'];

        if (0 !== \count($this->fieldIds)) {
            $where[] = 'tl_lead_data.master_id IN ('.implode(',', $this->fieldIds).')';
        }

        $data = [];
        $db = \Database::getInstance()->prepare("
            SELECT
                id,
                MAX(name) AS name,
                MAX(label) AS label,
                type,
                options,
                field_id,
                MAX(master_id) AS master_id,
                MAX(sorting) AS sorting
            FROM (
                SELECT
                    tl_lead_data.field_id AS id,
                    IFNULL(tl_form_field.name, tl_lead_data.name) AS name,
                    IF(tl_form_field.label IS NULL OR tl_form_field.label='', tl_lead_data.name, tl_form_field.label) AS label,
                    tl_form_field.type,
                    tl_form_field.options,
                    tl_lead_data.field_id,
                    tl_lead_data.master_id,
                    tl_lead_data.sorting
                FROM tl_lead_data
                LEFT JOIN tl_form_field ON tl_form_field.id=tl_lead_data.field_id
                LEFT JOIN tl_lead ON tl_lead_data.pid=tl_lead.id
                WHERE ".implode(' AND ', $where).'
                ORDER BY tl_lead.master_id!=tl_lead.form_id
            ) result_set
            GROUP BY field_id
            ORDER BY '.(!empty($this->fieldIds) ? \Database::getInstance()->findInSet('field_id', $this->fieldIds) : 'sorting')
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
        $cacheKey = $this->getCacheKey();

        if (array_key_exists($cacheKey, $this->getExportDataCache)) {
            return $this->getExportDataCache[$cacheKey];
        }

        $where = ['tl_lead.master_id=?'];

        if (0 !== \count($this->leadDataIds)) {
            $where[] = 'tl_lead.id IN('.implode(',', $this->leadDataIds).')';
        }

        if (null !== $this->getFrom()) {
            $where[] = 'tl_lead.created >= '.$this->getFrom();
        }

        if (null !== $this->getTo()) {
            $where[] = 'tl_lead.created <= '.$this->getTo();
        }

        $data = [];
        $db = \Database::getInstance()->prepare("
            SELECT
                tl_lead_data.*,
                tl_lead.created,
                tl_lead.form_id AS form_id,
                (SELECT title FROM tl_form WHERE id=tl_lead.form_id) AS form_name,
                tl_lead.member_id AS member_id,
                IFNULL((SELECT CONCAT(firstname, ' ', lastname) FROM tl_member WHERE id=tl_lead.member_id), '') AS member_name
            FROM tl_lead_data
            LEFT JOIN tl_lead ON tl_lead.id=tl_lead_data.pid
            WHERE ".implode(' AND ', $where).'
            ORDER BY tl_lead.created DESC
        ')->execute($this->formId);

        while ($db->next()) {
            $data[$db->pid][$db->field_id] = $db->row();
        }

        $this->getExportDataCache[$cacheKey] = $data;

        return $data;
    }

    /**
     * Get the header fields of the fields data.
     *
     * @return array
     */
    public function getHeaderFields()
    {
        $headerFields = [];

        foreach ($this->getFieldsData() as $fieldId => $row) {
            // Show single checkbox label as field label
            if ($row['label'] === $row['name']
                && 'checkbox' === $row['type']
                && '' !== $row['options']
            ) {
                $options = StringUtil::deserialize($row['options'], true);

                if (1 === \count($options)) {
                    $headerFields[$fieldId] = $options[0]['label'];
                    continue;
                }
            }

            $headerFields[$fieldId] = $row['label'];
        }

        return $headerFields;
    }
}
