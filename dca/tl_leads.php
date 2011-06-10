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
 * Table tl_leads
 */
$GLOBALS['TL_DCA']['tl_leads'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'closed'						=> true,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 1,
			'fields'					=> array('tstamp'),
			'flag'						=> 8,
			'panelLayout'				=> 'filter;search,limit',
		),
		'label' => array
		(
			'fields'					=> array('id'),
			'format'					=> '%s',
			'label_callback'			=> array('tl_leads', 'listLead')
		),
		'global_operations' => array
		(
			'groups' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['groups'],
				'href'					=> 'table=tl_lead_groups',
				'class'					=> 'header_lead_groups',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'fields' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['fields'],
				'href'					=> 'table=tl_lead_fields',
				'class'					=> 'header_lead_fields',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'export_csv' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['export_csv'],
				'href'					=> 'key=export_csv',
				'class'					=> 'header_export_csv',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_leads']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{meta_legend:hide},created,tstamp,group_id,form_id;{data_legend}',
	),

	// Fields
	'fields' => array
	(
		'created' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_leads']['created'],
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'datim', 'disabled'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
		),
		'tstamp' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_leads']['tstamp'],
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'datim', 'disabled'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
		),
		'group_id' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_leads']['group_id'],
			'filter'					=> true,
			'inputType'					=> 'text',
			'foreignKey'				=> 'tl_lead_groups.name',
			'eval'						=> array('disabled'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
			'load_callback' => array
			(
				array('tl_leads', 'loadGroupName'),
			),
		),
		'form_id' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_leads']['form_id'],
			'filter'					=> true,
			'inputType'					=> 'text',
			'foreignKey'				=> 'tl_form.title',
			'eval'						=> array('disabled'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
			'load_callback' => array
			(
				array('tl_leads', 'loadFormName'),
			),
		),
	)
);


class tl_leads extends Backend
{

	protected $arrGroups = array();
	
	public function listLead($row, $label)
	{
		if (!isset($arrGroups[$row['group_id']]))
		{
			$arrGroups[$row['group_id']] = $this->Database->prepare("SELECT * FROM tl_lead_groups WHERE id=?")->limit(1)->execute($row['group_id']);
		}
		
		$objGroup = $arrGroups[$row['group_id']];

		return $this->parseSimpleTokens(nl2br($objGroup->label), $row);
	}
	
	
	public function loadGroupName($varValue, $dc)
	{
		$objGroup = $this->Database->prepare("SELECT * FROM tl_lead_groups WHERE id=?")->execute($varValue);
		
		if ($objGroup->numRows)
		{
			return $objGroup->name;
		}
		else
		{
			return 'Deleted group (ID '.$varValue.')';
		}
	}
	
	
	public function loadFormName($varValue, $dc)
	{
		$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->execute($varValue);
		
		if ($objForm->numRows)
		{
			return $objForm->title;
		}
		else
		{
			return 'Deleted form (ID '.$varValue.')';
		}
	}
}

