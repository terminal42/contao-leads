<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Table tl_lead_fields
 */
$GLOBALS['TL_DCA']['tl_lead_fields'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'enableVersioning'			=> true,
		'closed'					=> true,
		'onload_callback'			=> array
		(
			array('Leads', 'allowEditing'),
			array('tl_lead_fields', 'disableFieldName'),
			array('tl_lead_fields', 'repairDatabaseFile'),
		),
		'onsubmit_callback' => array
		(
			array('tl_lead_fields', 'modifyColumn'),
//			array('tl_lead_fields', 'cleanFieldValues'),
		),
		'ondelete_callback' => array
		(
			array('tl_lead_fields', 'deleteField'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 0,
			'fields'				=> array('name'),
			'panelLayout'			=> 'limit'
		),
		'label' => array
		(
			'fields'				=> array('name', 'field_name', 'type'),
			'format'				=> '<div style="float:left; width:200px">%s</div><div style="float:left; width:200px; color:#b3b3b3;">%s</div><div style="color:#b3b3b3">[%s]</div>'
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'				=> 'table=',
				'class'				=> 'header_back',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_lead_fields']['new'],
				'href'				=> 'act=create',
				'class'				=> 'header_new',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_lead_fields']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif'
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_lead_fields']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_lead_fields']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_lead_fields']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type'),
		'default'					=> '{field_legend},name,field_name,type,legend',
		'text'						=> '{field_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},rgxp,maxlength,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
		'textarea'					=> '{field_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},rgxp,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
		'select'					=> '{field_legend},name,field_name,type,legend;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple,size;{search_filters_legend},fe_filter,fe_sorting,be_filter',
		'radio'						=> '{field_legend},name,field_name,type,legend;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting',
		'checkbox'					=> '{field_legend},name,field_name,type,legend;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple;{search_filters_legend},fe_filter,fe_sorting',
    ),

    // Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['name'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'field_name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['field_name'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>32, 'unique'=>true, 'doNotCopy'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
			'save_callback'			=> array
			(
				array('tl_lead_fields', 'createColumn'),
			),
		),
		'type' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['type'],
			'inputType'				=> 'select',
			'options'				=> array_keys($GLOBALS['LEAD_FFL']),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'reference'				=> &$GLOBALS['TL_LANG']['FFL'],
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['description'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>255, 'tl_class'=>'clr long'),
		),
		'options' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['options'],
			'inputType'				=> 'optionWizard',
			'eval'					=> array('tl_class'=>'clr'),
		),
		'foreignKey' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['foreignKey'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>64),
			'save_callback' => array
			(
				array('tl_lead_fields', 'validateForeignKey'),
			),
		),
		'search' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['search'],
			'inputType'				=> 'checkbox'
		),
		'filter' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['filter'],
			'inputType'				=> 'checkbox',
		),
		'mandatory' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['mandatory'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'multiple' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['multiple'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'size' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['size'],
			'inputType'				=> 'text',
			'default'				=> 5,
			'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'extensions' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['extensions'],
			'default'				=> 'jpg,jpeg,gif,png',
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'rte' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['rte'],
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_lead_fields', 'getRTE'),
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'rgxp' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['rgxp'],
			'inputType'				=> 'select',
			'options'				=> array('digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount', 'surcharge'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_lead_fields'],
			'eval'					=> array('helpwizard'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'maxlength' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_lead_fields']['maxlength'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50')
		),
	),
);


class tl_lead_fields extends Backend
{

	public function deleteField($dc)
	{
		if ($dc->id)
		{
			$objField = $this->Database->execute("SELECT * FROM tl_lead_fields WHERE id={$dc->id}");

			if ($this->Database->fieldExists($objField->field_name, 'tl_leads'))
			{
				$this->import('LeadDatabase');
				$this->LeadDatabase->delete($objField->field_name);
			}
		}
	}


	public function disableFieldName($dc)
	{
		if ($dc->id)
		{
			$objField = $this->Database->execute("SELECT * FROM tl_lead_fields WHERE id={$dc->id}");

			if ($objField->field_name != '')
			{
				$GLOBALS['TL_DCA']['tl_lead_fields']['fields']['field_name']['eval']['disabled'] = true;
				$GLOBALS['TL_DCA']['tl_lead_fields']['fields']['field_name']['eval']['mandatory'] = false;
			}
		}
	}


	public function createColumn($varValue, $dc)
	{
		$varValue = standardize($varValue, true);

		if (in_array($varValue, array('id', 'pid', 'tstamp', 'created')))
		{
			throw new Exception($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue);
		}

		if (strlen($varValue) && !$this->Database->fieldExists($varValue, 'tl_leads'))
		{
			$strType = strlen($GLOBALS['LEAD_FFL'][$this->Input->post('type')]['sql']) ? $this->Input->post('type') : 'text';

			$this->Database->query(sprintf("ALTER TABLE tl_leads ADD %s %s", $varValue, $GLOBALS['LEAD_FFL'][$strType]['sql']));

			$this->import('LeadDatabase');
			$this->LeadDatabase->add($varValue, $GLOBALS['LEAD_FFL'][$strType]['sql']);
		}

		return $varValue;
	}


	public function modifyColumn($dc)
	{
		$objField = $this->Database->execute("SELECT * FROM tl_lead_fields WHERE id={$dc->id}");

		if ($objField->field_name != '' && $dc->activeRecord->type != '' && $objField->type != $dc->activeRecord->type && $GLOBALS['LEAD_FFL'][$dc->activeRecord->type]['sql'] != '' && $this->Database->fieldExists($dc->activeRecord->field_name, 'tl_leads'))
		{
			$this->Database->query(sprintf("ALTER TABLE tl_leads MODIFY %s %s", $objField->field_name, $GLOBALS['LEAD_FFL'][$dc->activeRecord->type]['sql']));
		}
	}


	/**
	 * Remove field that are not available in certain field and could cause unwanted results
	 */
	public function cleanFieldValues($dc)
	{
		$strPalette = $GLOBALS['TL_DCA']['tl_lead_fields']['palettes'][$dc->activeRecord->type];

		if ($dc->activeRecord->variant_option && $GLOBALS['TL_DCA']['tl_lead_fields']['palettes'][$dc->activeRecord->type.'variant_option'] != '')
		{
			$strPalette = $GLOBALS['TL_DCA']['tl_lead_fields']['palettes'][$dc->activeRecord->type.'variant_option'];
		}

		$arrFields = array_keys($GLOBALS['TL_DCA']['tl_lead_fields']['fields']);
		$arrKeep = trimsplit(',|;', $strPalette);

		$arrClean = array_diff($arrFields, $arrKeep, array('pid', 'sorting'));

		$this->Database->execute("UPDATE tl_lead_fields SET " . implode("='', ", $arrClean) . "='' WHERE id={$dc->id}");
	}


	/**
	 * Returns a list of available rte config files
	 */
	public function getRTE($dc)
	{
		$arrOptions = array();

		foreach( scan(TL_ROOT . '/system/config') as $file )
		{
			if (is_file(TL_ROOT . '/system/config/' . $file) && strpos($file, 'tiny') === 0)
			{
				$arrOptions[] = basename($file, '.php');
			}
		}

		return $arrOptions;
	}


	/**
	 * Validate table and field of foreignKey
	 */
	public function validateForeignKey($varValue, $dc)
	{
		if ($varValue != '')
		{
			list($strTable, $strField) = explode('.', $varValue, 2);

			$this->Database->execute("SELECT $strField FROM $strTable");
		}

		return $varValue;
	}
	
	
	public function repairDatabaseFile($dc)
	{
		$this->import('LeadDatabase');
		$objFields = $this->Database->execute("SELECT * FROM tl_lead_fields");
		
		while( $objFields->next() )
		{
			$strType = strlen($GLOBALS['LEAD_FFL'][$objFields->type]['sql']) ? $objFields->type : 'text';
			$this->LeadDatabase->add($objFields->field_name, $GLOBALS['LEAD_FFL'][$strType]['sql']);
		}
	}
}

