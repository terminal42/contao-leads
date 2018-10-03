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

use Haste\Util\Format;
use Terminal42\LeadsBundle\Export\Utils\Row;
use Terminal42\LeadsBundle\Export\Utils\Tokens;

class Leads extends \Controller
{

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
