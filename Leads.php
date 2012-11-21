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


class Leads extends Controller
{

	/**
	 * Prepare a form value for storage in lead table
	 * @param mixed
	 * @param Database_Result
	 */
	public static function prepareValue($varValue, $objField)
	{
		// Run for all values in an array
		if (is_array($varValue))
		{
			foreach ($varValue as $k => $v)
			{
				$varValue[$k] = self::prepareValue($v, $objField);
			}

			return $varValue;
		}

		// Convert date formats into timestamps
		if ($varValue != '' && in_array($objField->rgxp, array('date', 'time', 'datim')))
		{
			$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$objField->rgxp . 'Format']);
			$varValue = $objDate->tstamp;
		}

		return $varValue;
	}


	/**
	 * Get the label for a form value to store in lead table
	 * @param mixed
	 * @param array
	 * @param Database_Result
	 */
	public static function prepareLabel($varValue, $arrOptions, $objField)
	{
		// Run for all values in an array
		if (is_array($varValue))
		{
			foreach ($varValue as $k => $v)
			{
				$varValue[$k] = self::prepareLabel($v, $arrOptions, $objField);
			}

			return $varValue;
		}

		foreach ($arrOptions as $arrOption)
		{
			if ($arrOption['value'] == $varValue && $arrOption['label'] != '')
			{
				return $arrOption['label'];
			}
		}

		return $varValue;
	}


	/**
	 * Format a lead field for list view
	 * @param object
	 * @return string
	 */
	public static function formatValue($objData)
	{
		$strValue = implode(', ', deserialize($objData->value, true));

		if ($objData->label != '')
		{
			$strLabel = $objData->label;
			$arrLabel = deserialize($objData->label);

			if (is_array($arrLabel) && !empty($arrLabel))
			{
				$strLabel = implode(', ', $arrLabel);
			}

			$strValue = $strLabel . ' (' . $strValue . ')';
		}

		return $strValue;
	}


	/**
	 * Construct object and import frontend user
	 */
	public function __construct()
	{
		parent::__construct();

		$this->import('Database');

		if (FE_USER_LOGGED_IN === true)
		{
			$this->import('FrontendUser', 'User');
		}
	}


	/**
	 * Dynamically load the name for the current lead view
	 * @param string
	 * @param string
	 */
	public function loadLeadName($strName, $strLanguage)
	{
		if ($strName == 'modules' && $this->Input->get('do') == 'lead')
		{
			$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->execute($this->Input->get('master'));

			$GLOBALS['TL_LANG']['MOD']['lead'][0] = $objForm->leadMenuLabel ? $objForm->leadMenuLabel : $objForm->title;
		}
	}


	/**
	 * Add leads to the backend navigation
	 * @param array
	 * @param bool
	 * @return array
	 */
	public function loadBackendModules($arrModules, $blnShowAll)
	{
		if (!$this->Database->tableExists('tl_lead'))
		{
			unset($arrModules['leads']);
			return $arrModules;
		}

		$objForms = $this->Database->execute("SELECT f.id, f.title, IF(f.leadMenuLabel='', f.title, f.leadMenuLabel) AS leadMenuLabel FROM tl_form f LEFT JOIN tl_lead l ON l.master_id=f.id WHERE leadEnabled='1' AND leadMaster=0
											  UNION
											  SELECT l.master_id AS id, IFNULL(f.title, CONCAT('ID ', l.master_id)) AS title, IFNULL(IF(f.leadMenuLabel='', f.title, f.leadMenuLabel), CONCAT('ID ', l.master_id)) AS leadMenuLabel FROM tl_lead l LEFT JOIN tl_form f ON l.master_id=f.id WHERE ISNULL(f.id)
											  ORDER BY leadMenuLabel");

		if (!$objForms->numRows)
		{
			unset($arrModules['leads']);
		}

		$arrSession = $this->Session->get('backend_modules');
		$blnOpen = $arrSession['leads'] || $blnShowAll;
		$arrModules['leads']['modules'] = array();

		if ($blnOpen)
		{
			while ($objForms->next())
			{
				$arrModules['leads']['modules']['lead_'.$objForms->id] = array
				(
					'tables'	=> array('tl_lead'),
					'title'		=> specialchars(sprintf($GLOBALS['TL_LANG']['MOD']['leads'][1], $objForms->title)),
	                'label'		=> $objForms->leadMenuLabel,
	                'icon'		=> 'style="background-image:url(\'system/modules/leads/assets/icon.png\')"',
	                'class'		=> 'navigation leads',
	                'href'		=> 'contao/main.php?do=lead&master='.$objForms->id,
				);
			}
		}

		return $arrModules;
	}


	/**
	 * Process data submitted through the form generator
	 * @param array
	 * @param array
	 * @param array
	 */
	public function processFormData($arrPost, $arrForm, $arrFiles)
	{
		if ($arrForm['leadEnabled'])
		{
			$time = time();

			$intLead = $this->Database->prepare("INSERT INTO tl_lead (tstamp,created,language,form_id,master_id,member_id) VALUES (?,?,?,?,?,?)")
									  ->executeUncached($time, $time, $GLOBALS['TL_LANGUAGE'], $arrForm['id'], ($arrForm['leadMaster'] ? $arrForm['leadMaster'] : $arrForm['id']), (FE_USER_LOGGED_IN === true ? $this->User->id : 0))
									  ->insertId;


			// Fetch master form fields
			if ($arrForm['leadMaster'] > 0)
			{
				$objFields = $this->Database->prepare("SELECT f2.*, f1.id AS master_id, f1.name AS postName FROM tl_form_field f1 LEFT JOIN tl_form_field f2 ON f1.leadStore=f2.id WHERE f1.pid=? AND f1.leadStore>0 AND f2.leadStore='1' ORDER BY f2.sorting")->execute($arrForm['id']);
			}
			else
			{
				$objFields = $this->Database->prepare("SELECT *, id AS master_id, name AS postName FROM tl_form_field WHERE pid=? AND leadStore='1' ORDER BY sorting")->execute($arrForm['id']);
			}

			while ($objFields->next())
			{
				if (isset($arrPost[$objFields->postName]))
				{
					$varLabel = '';
					$varValue = $arrPost[$objFields->postName];

					if ($objFields->options != '')
					{
						$arrOptions = deserialize($objFields->options, true);
						$varLabel = Leads::prepareLabel($varValue, $arrOptions, $objFields);
					}

					$varValue = Leads::prepareValue($varValue, $objFields);

					$arrSet = array
					(
						'pid'			=> $intLead,
						'sorting'		=> $objFields->sorting,
						'tstamp'		=> $time,
						'master_id'		=> $objFields->master_id,
						'field_id'		=> $objFields->id,
						'name'			=> $objFields->name,
						'value'			=> $varValue,
						'label'			=> $varLabel,
					);


					// @todo Trigger hook


					$this->Database->prepare("INSERT INTO tl_lead_data %s")
								   ->set($arrSet)
								   ->executeUncached();
				}
			}
		}
	}
}

