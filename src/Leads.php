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

use Contao\File;
use Terminal42\LeadsBundle\Exporter\ExporterInterface;
use Terminal42\LeadsBundle\Exporter\Utils\Row;
use Terminal42\LeadsBundle\Exporter\Utils\Tokens;

class Leads extends \Controller
{
    /**
     * Prepare a form value for storage in lead table.
     *
     * @param mixed                   $varValue
     * @param \Database\Result|object $objField
     *
     * @return array|int
     */
    public static function prepareValue($varValue, $objField)
    {
        // File upload
        if ('upload' === $objField->type) {
            return $varValue['uuid'];
        }

        // Run for all values in an array
        if (is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = self::prepareValue($v, $objField);
            }

            return $varValue;
        }

        $varValue = static::convertRgxp($varValue, $objField->rgxp);

        return $varValue;
    }


    /**
     * Get the label for a form value to store in lead table.
     *
     * @param mixed                   $varValue
     * @param \Database\Result|object $objField
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
        if ('upload' === $objField->type) {
            $objFile = \FilesModel::findByUuid($varValue);

            if ($objFile !== null) {
                return $objFile->path;
            }
        }

        $varValue = static::convertRgxp($varValue, $objField->rgxp);

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
     * @param object $objData
     *
     * @return string
     */
    public static function formatValue($objData)
    {
        $fieldModel = \FormFieldModel::findByPk($objData->field_id);

        if (null !== $fieldModel) {
            $data = $fieldModel->row();
            $data['eval'] = $fieldModel->row();
            $strValue = Format::dcaValueFromArray($data, $objData->value);
            $strLabel = Format::dcaLabelFromArray($data);

            return $strLabel . ' <span style="color:#b3b3b3; padding-left:3px;">[' . $strValue . ']</span>';
        }

        $strValue = implode(', ', deserialize($objData->value, true));

        if ($objData->label != '' && $objData->label != $objData->value)  {
            $strLabel = $objData->label;
            $arrLabel = deserialize($objData->label, true);

            if (!empty($arrLabel)) {
                $strLabel = implode(', ', $arrLabel);
            }

            if ($strValue == '') {
                return $strLabel;
            }

            $strValue = $strLabel . ' <span style="color:#b3b3b3; padding-left:3px;">[' . $strValue . ']</span>';
        }

        return $strValue;
    }


    /**
     * Dynamically load the name for the current lead view.
     *
     * @param string $strName
     */
    public function loadLeadName($strName)
    {
        if ('modules' === $strName && 'lead' === \Input::get('do')) {
            $objForm = \Database::getInstance()
                ->prepare("SELECT * FROM tl_form WHERE id=?")
                ->execute(\Input::get('master'))
            ;

            $GLOBALS['TL_LANG']['MOD']['lead'][0] = $objForm->leadMenuLabel ?: $objForm->title;
        }
    }

    /**
     * Process data submitted through the form generator.
     *
     * @param array $arrPost
     * @param array $arrForm
     * @param array $arrFiles
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
                ($arrForm['leadMaster'] ?: $arrForm['id']),
                (FE_USER_LOGGED_IN === true ? \FrontendUser::getInstance()->id : 0),
                serialize($arrPost)
            )->insertId;

            // Fetch master form fields
            if ($arrForm['leadMaster'] > 0) {
                $objFields = \Database::getInstance()
                    ->prepare("SELECT f2.*, f1.id AS master_id, f1.name AS postName FROM tl_form_field f1 LEFT JOIN tl_form_field f2 ON f1.leadStore=f2.id WHERE f1.pid=? AND f1.leadStore>0 AND f2.leadStore='1' AND f1.invisible='' ORDER BY f2.sorting")
                    ->execute($arrForm['id'])
                ;
            } else {
                $objFields = \Database::getInstance()
                    ->prepare("SELECT *, id AS master_id, name AS postName FROM tl_form_field WHERE pid=? AND leadStore='1' AND invisible='' ORDER BY sorting")
                    ->execute($arrForm['id'])
                ;
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
                            $this->{$callback[0]}->{$callback[1]}($arrPost, $arrForm, $arrFiles, $intLead, $objFields, $arrSet);
                        }
                    }

                    \Database::getInstance()->prepare("INSERT INTO tl_lead_data %s")->set($arrSet)->execute();
                }
            }

            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['storeLeadsData']) && is_array($GLOBALS['TL_HOOKS']['storeLeadsData'])) {
                foreach ($GLOBALS['TL_HOOKS']['storeLeadsData'] as $callback) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($arrPost, $arrForm, $arrFiles, $intLead, $objFields);
                }
            }
        }
    }


    /**
     * Export the data.
     *
     * @param integer         $intConfig
     * @param array           $arrIds
     *
     * @return File
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function export($intConfig, $arrIds=null)
    {
        /** @var \Database\Result|object $objConfig */
        $objConfig = \Database::getInstance()
            ->prepare("
                SELECT *,
                (SELECT leadMaster FROM tl_form WHERE tl_form.id=tl_lead_export.pid) AS master
                FROM tl_lead_export
                WHERE id=?
            ")
            ->limit(1)
            ->execute($intConfig)
        ;

        if (!$objConfig->numRows) {
            throw new \InvalidArgumentException(sprintf('Export config ID %s not found', $intConfig));
        }

        /** @var ExporterInterface $exporter */
        $exporterClass = $GLOBALS['LEADS_EXPORT'][$objConfig->type];

        if (!class_exists($exporterClass) || !($exporter = new $exporterClass()) instanceof ExporterInterface) {
            throw new \RuntimeException(sprintf('Invalid export type: %s (%s)', $objConfig->type, $exporterClass));
        }

        $objConfig->master      = $objConfig->master ?: $objConfig->pid;
        $objConfig->fields      = deserialize($objConfig->fields, true);
        $objConfig->tokenFields = deserialize($objConfig->tokenFields, true);

        return $exporter->export($objConfig, $arrIds);
    }

    /**
     * Handles the system columns when exporting.
     *
     * @param array $columnConfig
     * @param array $data
     *
     * @return null|string
     */
    public function handleSystemColumnExports($columnConfig, $data)
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
     * @param array $columnConfig
     * @param array $data
     * @param array $config
     *
     * @return string
     */
    public function handleTokenExports($columnConfig, $data, $config)
    {

        if ('tokens' !== $config->export) {

            return null;
        }

        $tokens = array();

        foreach ($columnConfig['allFieldsConfig'] as $fieldConfig) {

            $value = '';

            if (isset($data[$fieldConfig['id']])) {

                $value = $data[$fieldConfig['id']]['value'];
                $value = deserialize($value);

                // Add multiple tokens (<fieldname>_<option_name>) for multi-choice fields
                if (is_array($value)) {
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
     * @param string $value
     * @param string $rgxp
     *
     * @return string
     */
    private static function convertRgxp($value, $rgxp)
    {
        // Convert date formats into timestamps
        if (!empty($value)
            && in_array($rgxp, array('date', 'time', 'datim'))
            && \Validator::{'is'.ucfirst($rgxp)}($value)
        ) {
            $format = \Date::{'getNumeric'.ucfirst($rgxp).'Format'}();
            $date = new \Date($value, $format);

            return (string) $date->tstamp;
        }

        return $value;
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
