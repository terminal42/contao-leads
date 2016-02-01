<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle;

use Terminal42\LeadsBundle\Exporter\ExporterInterface;
use Terminal42\LeadsBundle\Exporter\Utils\Row;
use Terminal42\LeadsBundle\Exporter\Utils\Tokens;

class Leads extends \Controller
{
    /**
     * Prepare a form value for storage in lead table.
     *
     * @param mixed
     * @param \Database\Result
     */
    public static function prepareValue($varValue, $objField)
    {
        // File upload
        if ($objField->type == 'upload') {
            return $varValue['uuid'];
        }

        // Run for all values in an array
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = self::prepareValue($v, $objField);
            }

            return $varValue;
        }

        // Convert date formats into timestamps
        if ($varValue != '' && in_array($objField->rgxp, array('date', 'time', 'datim'))) {
            $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$objField->rgxp . 'Format']);
            $varValue = $objDate->tstamp;
        }

        return $varValue;
    }


    /**
     * Get the label for a form value to store in lead table.
     *
     * @param mixed $varValue
     * @param \Database\Result $objField
     *
     * @return mixed
     */
    public static function prepareLabel($varValue, $objField)
    {
        // Run for all values in an array
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = self::prepareLabel($v, $objField);
            }

            return $varValue;
        }

        // File upload
        if ($objField->type == 'upload') {
            $objFile = \FilesModel::findByUuid($varValue);

            if ($objFile !== null) {
                return $objFile->path;
            }
        }

        // Convert timestamps into date format
        if ($varValue != '' && in_array($objField->rgxp, array('date', 'time', 'datim'))) {
            $varValue = \Date::parse($GLOBALS['TL_CONFIG'][$objField->rgxp . 'Format'], $varValue);
        }

        if ($objField->options != '') {
            $arrOptions = deserialize($objField->options, true);

            foreach ($arrOptions as $arrOption) {
                if ($arrOption['value'] == $varValue && $arrOption['label'] != '') {
                    $varValue = $arrOption['label'];
                }
            }
        }

        return $varValue;
    }


    /**
     * Format a lead field for list view.
     *
     * @param object
     *
     * @return string
     */
    public static function formatValue($objData)
    {
        $strValue = implode(', ', deserialize($objData->value, true));

        if ($objData->label != '') {
            $strLabel = $objData->label;
            $arrLabel = deserialize($objData->label, true);

            if (!empty($arrLabel)) {
                $strLabel = implode(', ', $arrLabel);
            }

            $strValue = $strLabel . ' <span style="color:#b3b3b3; padding-left:3px;">[' . $strValue . ']</span>';
        }

        return $strValue;
    }


    /**
     * Dynamically load the name for the current lead view.
     *
     * @param string
     * @param string
     */
    public function loadLeadName($strName, $strLanguage)
    {
        if ($strName == 'modules' && $this->Input->get('do') == 'lead') {
            $objForm = \Database::getInstance()->prepare("SELECT * FROM tl_form WHERE id=?")->execute(\Input::get('master'));

            $GLOBALS['TL_LANG']['MOD']['lead'][0] = $objForm->leadMenuLabel ? $objForm->leadMenuLabel : $objForm->title;
        }
    }


    /**
     * Add leads to the backend navigation.
     *
     * @param array
     * @param bool
     *
     * @return array
     */
    public function loadBackendModules($arrModules, $blnShowAll)
    {
        if (!\Database::getInstance()->tableExists('tl_lead')) {
            unset($arrModules['leads']);
            return $arrModules;
        }

        $arrIds = array();
        $objUser = \BackendUser::getInstance();

        // Check permissions
        if (!$objUser->isAdmin) {
            if (!is_array($objUser->forms) || empty($objUser->forms)) {
                unset($arrModules['leads']);
                return $arrModules;
            }

            $arrIds = $objUser->forms;
        }

        $objForms = \Database::getInstance()->execute("
                SELECT f.id, f.title, IF(f.leadMenuLabel='', f.title, f.leadMenuLabel) AS leadMenuLabel
                FROM tl_form f
                LEFT JOIN tl_lead l ON l.master_id=f.id
                WHERE leadEnabled='1' AND leadMaster=0" . (!empty($arrIds) ? (" AND f.id IN (" . implode(',', array_map('intval', $arrIds)) . ")") : "") . "
            UNION
                SELECT l.master_id AS id, IFNULL(f.title, CONCAT('ID ', l.master_id)) AS title, IFNULL(IF(f.leadMenuLabel='', f.title, f.leadMenuLabel), CONCAT('ID ', l.master_id)) AS leadMenuLabel
                FROM tl_lead l
                LEFT JOIN tl_form f ON l.master_id=f.id
                WHERE ISNULL(f.id)
                ORDER BY leadMenuLabel
        ");

        if (!$objForms->numRows) {
            unset($arrModules['leads']);

            return $arrModules;
        }

        $arrSession = $this->Session->get('backend_modules');
        $blnOpen = $arrSession['leads'] || $blnShowAll;
        $arrModules['leads']['modules'] = array();

        if ($blnOpen) {
            while ($objForms->next()) {

                $arrModules['leads']['modules']['lead_'.$objForms->id] = array(
                    'tables'    => array('tl_lead'),
                    'title'     => specialchars(sprintf($GLOBALS['TL_LANG']['MOD']['leads'][1], $objForms->title)),
                    'label'     => $objForms->leadMenuLabel,
                    'icon'      => 'style="background-image:url(\'system/modules/leads/assets/icon.png\')"',
                    'class'     => 'navigation leads',
                    'href'      => 'contao/main.php?do=lead&master='.$objForms->id,
                );
            }
        } else {
            $arrModules['leads']['modules'] = false;
            $arrModules['leads']['icon'] = 'modPlus.gif';
            $arrModules['leads']['title'] = specialchars($GLOBALS['TL_LANG']['MSC']['expandNode']);
        }

        return $arrModules;
    }


    /**
     * Process data submitted through the form generator.
     *
     * @param array
     * @param array
     * @param array
     */
    public function processFormData(&$arrPost, &$arrForm, &$arrFiles)
    {
        if ($arrForm['leadEnabled']) {
            $time = time();

            $intLead = \Database::getInstance()->prepare("
                INSERT INTO tl_lead (tstamp,created,language,form_id,master_id,member_id,post_data) VALUES (?,?,?,?,?,?,?)
            ")->execute(
                $time,
                $time,
                $GLOBALS['TL_LANGUAGE'],
                $arrForm['id'],
                ($arrForm['leadMaster'] ? $arrForm['leadMaster'] : $arrForm['id']),
                (FE_USER_LOGGED_IN === true ? \FrontendUser::getInstance()->id : 0),
                serialize($arrPost)
            )->insertId;


            // Fetch master form fields
            if ($arrForm['leadMaster'] > 0) {
                $objFields = \Database::getInstance()->prepare("SELECT f2.*, f1.id AS master_id, f1.name AS postName FROM tl_form_field f1 LEFT JOIN tl_form_field f2 ON f1.leadStore=f2.id WHERE f1.pid=? AND f1.leadStore>0 AND f2.leadStore='1' ORDER BY f2.sorting")->execute($arrForm['id']);
            } else {
                $objFields = \Database::getInstance()->prepare("SELECT *, id AS master_id, name AS postName FROM tl_form_field WHERE pid=? AND leadStore='1' ORDER BY sorting")->execute($arrForm['id']);
            }

            while ($objFields->next()) {
                $arrSet = array();

                // Regular data
                if (isset($arrPost[$objFields->postName])) {
                    $varValue = Leads::prepareValue($arrPost[$objFields->postName], $objFields);
                    $varLabel = Leads::prepareLabel($varValue, $objFields);

                    $arrSet = array(
                        'pid'       => $intLead,
                        'sorting'   => $objFields->sorting,
                        'tstamp'    => $time,
                        'master_id' => $objFields->master_id,
                        'field_id'  => $objFields->id,
                        'name'      => $objFields->name,
                        'value'     => $varValue,
                        'label'     => $varLabel,
                    );
                }

                // Files
                if (isset($arrFiles[$objFields->postName]) && $arrFiles[$objFields->postName]['uploaded']) {
                    $varValue = Leads::prepareValue($arrFiles[$objFields->postName], $objFields);
                    $varLabel = Leads::prepareLabel($varValue, $objFields);

                    $arrSet = array(
                        'pid'       => $intLead,
                        'sorting'   => $objFields->sorting,
                        'tstamp'    => $time,
                        'master_id' => $objFields->master_id,
                        'field_id'  => $objFields->id,
                        'name'      => $objFields->name,
                        'value'     => $varValue,
                        'label'     => $varLabel,
                    );
                }

                if (!empty($arrSet)) {
                    // HOOK: add custom logic
                    if (isset($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore']) && is_array($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore'])) {
                        foreach ($GLOBALS['TL_HOOKS']['modifyLeadsDataOnStore'] as $callback) {
                            $this->import($callback[0]);
                            $this->$callback[0]->$callback[1]($arrPost, $arrForm, $arrFiles, $intLead, $objFields, $arrSet);
                        }
                    }

                    \Database::getInstance()->prepare("INSERT INTO tl_lead_data %s")->set($arrSet)->executeUncached();
                }
            }

            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['storeLeadsData']) && is_array($GLOBALS['TL_HOOKS']['storeLeadsData'])) {
                foreach ($GLOBALS['TL_HOOKS']['storeLeadsData'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($arrPost, $arrForm, $arrFiles, $intLead, $objFields);
                }
            }
        }
    }


    /**
     * Export the data.
     *
     * @param integer $intConfig
     * @param array
     */
    public static function export($intConfig, $arrIds=null)
    {
        $objConfig = \Database::getInstance()->prepare("SELECT *, (SELECT leadMaster FROM tl_form WHERE tl_form.id=tl_lead_export.pid) AS master FROM tl_lead_export WHERE id=?")
                                            ->limit(1)
                                            ->execute($intConfig);

        if (!$objConfig->numRows || !isset($GLOBALS['LEADS_EXPORT'][$objConfig->type])) {
            return;
        }

        $objConfig->master = $objConfig->master ?: $objConfig->pid;
        $arrFields = array();

        $exporterDefinition = $GLOBALS['LEADS_EXPORT'][$objConfig->type];

        // Backwards compatibility
        if (is_array($exporterDefinition)) {
            // Prepare the fields
            foreach (deserialize($objConfig->fields, true) as $arrField) {
                $arrFields[$arrField['field']] = $arrField;
            }

            $objConfig->fields = $arrFields;

            $objExport = $exporterDefinition[0]();
            $objExport->$exporterDefinition[1]($objConfig, $arrIds);
        } else {

            // Note the difference here: Fields are not touched and thus every field can be exported multiple times
            $exporter = new $exporterDefinition();

            $objConfig->fields      = deserialize($objConfig->fields, true);
            $objConfig->tokenFields = deserialize($objConfig->tokenFields, true);

            if ($exporter instanceof ExporterInterface) {
                $exporter->export($objConfig, $arrIds);
            }
        }
    }

    /**
     * Handles the system columns when exporting.
     *
     * @param $columnConfig
     * @param $data
     * @param $config
     * @param $value
     */
    public function handleSystemColumnExports($columnConfig, $data, $config, $value)
    {
        $systemColumns = static::getSystemColumns();

        if (isset($columnConfig['field'])
            && in_array($columnConfig['field'], array_keys($systemColumns))
        ) {

            if ($columnConfig['field'] === '_field') {

                return null;
            }

            $firstEntry = reset($data);
            $systemColumnConfig = $systemColumns[$columnConfig['field']];

            $value = (isset($systemColumnConfig['valueColRef']) ? $firstEntry[$systemColumnConfig['valueColRef']] : null);
            $value =  Row::transformValue($value, $systemColumnConfig);

            return Row::getValueForOutput(
                $systemColumnConfig['value'],
                $value,
                (isset($systemColumnConfig['labelColRef']) ? $firstEntry[$systemColumnConfig['labelColRef']] : null)
            );
        }
    }

    /**
     * Handles the Simple Tokens and Insert Tags when exporting.
     *
     * @param $columnConfig
     * @param $data
     * @param $config
     * @param $value
     */
    public function handleTokenExports($columnConfig, $data, $config, $value)
    {

        if ($config->export != 'tokens') {

            return null;
        }

        $tokens = array();

        foreach ($columnConfig['allFieldsConfig'] as $fieldConfig) {

            $value = '';

            if (isset($data[$fieldConfig['id']])) {

                $value = $data[$fieldConfig['id']]['value'];
                $value = deserialize($value, true);

                // Add multiple tokens (<fieldname>_<option_name>) for multi-choice fields
                if (count($value) > 1) {
                    foreach ($value as $choice) {
                        $tokens[$fieldConfig['name'] . '_' . $choice] = 1;
                    }
                }

                $value = Row::transformValue($data[$fieldConfig['id']]['value'], $fieldConfig);
            }

            $tokens[$fieldConfig['name']] = $value;
        }

        return Tokens::recursiveReplaceTokensAndTags($columnConfig['tokensValue'], $tokens);
    }

    /**
     * Default system columns.
     *
     * @return array
     */
    public static function getSystemColumns()
    {
        \System::loadLanguageFile('tl_lead_export');

        return array(
            '_form' => array(
                'field'         => '_form',
                'name'          => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
                'value'         => 'all',
                'format'        => 'raw',
                'valueColRef'   => 'form_id',
                'labelColRef'   => 'form_name'
            ),
            '_created' => array(
                'field'         => '_created',
                'name'          => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
                'value'         => 'value',
                'format'        => 'datim',
                'valueColRef'   => 'created'
            ),
            '_member' => array(
                'field'         => '_member',
                'name'          => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
                'value'         => 'all',
                'format'        => 'raw',
                'valueColRef'   => 'member_id',
                'labelColRef'   => 'member_name'
            ),
            '_skip' => array(
                'field'         => '_skip',
                'name'          => $GLOBALS['TL_LANG']['tl_lead_export']['field_skip'],
                'value'         => 'value',
                'format'        => 'raw'
            )
        );
    }
}
