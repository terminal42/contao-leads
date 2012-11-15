<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
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
 * @copyright  terminal42 gmbh 2011-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_lead
 */
$GLOBALS['TL_DCA']['tl_lead'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'closed'						=> true,
		'notEditable'					=> true,
		'ctable'						=> array('tl_lead_data'),
		'onload_callback' => array
		(
			array('tl_lead', 'checkPermission'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 1,
			'fields'					=> array('created'),
			'flag'						=> 8,
			'panelLayout'				=> 'filter,limit',
		),
		'label' => array
		(
			'fields'					=> array('form_id'),
			'format'					=> '%s',
			'label_callback'			=> array('tl_lead', 'getLabel'),
		),
		'operations' => array
		(
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Fields
	'fields' => array
	(
		'form_id' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_lead']['form_id'],
			'filter'					=> true,
			'foreignKey'				=> 'tl_form.title',
		),
	)
);


class tl_lead extends Backend
{

	/**
	 * Check if a user has access to lead data
	 * @param DataContainer
	 */
	public function checkPermission($dc)
	{
		if ($this->Input->get('master') == '')
		{
			$this->redirect('contao/main.php?act=error');
		}
	}


	/**
	 * Generate label for this record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function getLabel($row, $label)
	{
		$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->execute($row['master_id']);

		// No form found, we can't format the label
		if (!$objForm->numRows)
		{
			return $label;
		}

		$arrTokens = array();
		$objData = $this->Database->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

		while ($objData->next())
		{
			$varValue = deserialize($objData->value);
			$arrTokens[$objData->name] = is_array($varValue) ? implode(', ', $varValue) : $varValue;
		}

		return $this->parseSimpleTokens($objForm->leadLabel, $arrTokens);
	}
}

