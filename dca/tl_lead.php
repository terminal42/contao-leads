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
			'filter'					=> array(array('master_id=?', $this->Input->get('master'))),
		),
		'label' => array
		(
			'fields'					=> array('created'),
			'format'					=> &$GLOBALS['TL_LANG']['tl_lead']['label_format'],
			'label_callback'			=> array('tl_lead', 'getLabel'),
		),
		'global_operations' => array
		(
			'export' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['export'],
				'class'					=> 'header_leads_export',
				'attributes'			=> 'onclick="Backend.getScrollOffset();" style="display:none"',
			),
			'export_csv' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['export_csv'],
				'href'					=> 'key=export&amp;type=csv',
				'class'					=> 'leads-export header_export_csv',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'export_excel' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['export_excel'],
				'href'					=> 'key=export&amp;type=excel',
				'class'					=> 'leads-export header_export_excel',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();" accesskey="e"'
			),
		),
		'operations' => array
		(
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['show'],
				'href'					=> 'key=show',
				'icon'					=> 'show.gif'
			),
			'data' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_lead']['data'],
				'href'					=> 'table=tl_lead_data',
				'icon'					=> 'system/modules/leads/assets/field.png'
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
		'created' => array
		(
			'flag'						=> 8,
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


	public function show($dc)
	{
		$objData = $this->Database->prepare("SELECT d.*, l.created, f.title AS form_title, IF(ff.label IS NULL OR ff.label='', d.name, ff.label) AS name FROM tl_lead l LEFT JOIN tl_lead_data d ON l.id=d.pid LEFT OUTER JOIN tl_form f ON l.master_id=f.id LEFT OUTER JOIN tl_form_field ff ON d.master_id=ff.id WHERE l.id=? ORDER BY d.sorting")->execute($dc->id);

		if (!$objData->numRows)
		{
			$this->redirect('contao/main.php?act=error');
		}

		$i = 0;
		$rows = '';

		while ($objData->next())
		{
			$rows .= '
  <tr>
    <td' . ($i%2 ? ' class="tl_bg"' : '') . '><span class="tl_label">' . $objData->name . ': </span></td>
    <td' . ($i%2 ? ' class="tl_bg"' : '') . '>' . Leads::formatValue($objData) . '</td>
  </tr>';

  			++$i;
		}


		return '
<div id="tl_buttons">
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], 'ID ' . $dc->id) . '</h2>

<table class="tl_show">
  <tbody><tr>
    <td><span class="tl_label">ID: </span></td>
    <td>' . $dc->id . '</td>
  </tr>
  <tr>
    <td class="tl_bg"><span class="tl_label">' . $GLOBALS['TL_LANG']['tl_lead']['created'][0] . ': </span></td>
    <td class="tl_bg">' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objData->created) . '</td>
  </tr>
  <tr>
    <td><span class="tl_label">' . $GLOBALS['TL_LANG']['tl_lead']['form_id'][0] . ': </span></td>
    <td>' . $objData->form_title . '</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>' . $rows . '
</tbody></table>
';
	}


	public function export()
	{
		$intMaster = $this->Input->get('master');

		if (!$intMaster)
		{
			$this->redirect('contao/main.php?act=error');
		}

		$this->import('Leads');
		$this->Leads->export($intMaster);
	}
}

