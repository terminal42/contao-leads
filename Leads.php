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


class Leads extends Backend
{
	
	/**
	 * DCA is "closed" to hide the "new" button. Re-enable it when clicking on a button
	 *
	 * @param  object
	 * @return void
	 */
	public function allowEditing($dc)
	{
		if ($this->Input->get('act') != '')
		{
			$GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = false;
		}
	}
	
	
	public function loadFields($strTable)
	{
		if ($strTable != 'tl_leads')
			return;
		
		$strCondition = '';
		if ($this->Input->get('id') != '')
		{
			$objGroup = $this->Database->prepare("SELECT * FROM tl_lead_groups WHERE id=(SELECT group_id FROM tl_leads WHERE id=?)")->execute($this->Input->get('id'));
			$arrFields = deserialize($objGroup->fields);
				
			if (is_array($arrFields) && count($arrFields))
			{
				$strCondition = ' WHERE id IN (' . implode(',', $arrFields) . ')  ORDER BY id=' . implode(' DESC, id=', $arrFields) . ' DESC';
			}
		}
		
		$objFields = $this->Database->execute("SELECT * FROM tl_lead_fields" . $strCondition);

		while ( $objFields->next() )
		{
			// Add to palette
			$GLOBALS['TL_DCA']['tl_leads']['palettes']['default'] .= ','.$objFields->field_name;
			
			// Keep field settings made through DCA code
			$arrData = is_array($GLOBALS['TL_DCA']['tl_leads']['fields'][$objFields->field_name]) ? $GLOBALS['TL_DCA']['tl_leads']['fields'][$objFields->field_name] : array();

			$arrData['label']		= array($objFields->name, $objFields->description);
			$arrData['inputType']	= $objFields->type;
			$arrData['eval']		= is_array($arrData['eval']) ? array_merge($arrData['eval'], $objFields->row()) : $objFields->row();

			if ($objFields->filter) $arrData['filter'] = true;
			if ($objFields->search) $arrData['search'] = true;

			// Add date picker
			if ($objFields->rgxp == 'date')
			{
				$arrData['eval']['datepicker'] = $this->getDatePickerString();
			}

			if ($objFields->type == 'textarea' || $objFields->rte != '')
			{
				$arrData['eval']['tl_class'] = 'clr';
			}

			// Prepare options
			if ($objFields->foreignKey != '')
			{
				$arrData['foreignKey'] = $objFields->foreignKey;
				$arrData['eval']['includeBlankOption'] = true;
				unset($arrData['options']);
			}
			else
			{
				$arrOptions = deserialize($objFields->options);
				
				if (is_array($arrOptions) && count($arrOptions))
				{
					$arrData['options'] = array();
					$arrData['reference'] = array();

					$strGroup = '';
					foreach ($arrOptions as $option)
					{
						if (!strlen($option['value']))
						{
							$arrData['eval']['includeBlankOption'] = true;
							$arrData['eval']['blankOptionLabel'] = $option['label'];
							continue;
						}
						elseif ($option['group'])
						{
							$strGroup = $option['value'];
							continue;
						}

						if (strlen($strGroup))
						{
							$arrData['options'][$strGroup][$option['value']] = $option['label'];
						}
						else
						{
							$arrData['options'][$option['value']] = $option['label'];
						}

						$arrData['reference'][$option['value']] = $option['label'];
					}
				}
			}

			unset($arrData['eval']['foreignKey']);
			unset($arrData['eval']['options']);

			if (is_array($GLOBALS['ISO_ATTR'][$objFields->type]['callback']) && count($GLOBALS['ISO_ATTR'][$objFields->type]['callback']))
			{
				foreach( $GLOBALS['ISO_ATTR'][$objFields->type]['callback'] as $callback )
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($objFields->field_name, $arrData);
				}
			}

			$GLOBALS['TL_DCA']['tl_leads']['fields'][$objFields->field_name] = $arrData;
		}
	}
	
	
	public function injectFieldSelect($dc)
	{
		if ($this->Input->get('act') != 'edit')
			return;
		
		$objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id={$dc->id})");
		
		if ($objForm->leadGroup)
		{
			foreach( $GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $strName => $strPalette )
			{
				if (in_array($strName, array('__selector__', 'submit', 'default', 'headline', 'explanation')))
					continue;
			
				$GLOBALS['TL_DCA']['tl_form_field']['palettes'][$strName] = str_replace(',type,', ',type,leadField,', $strPalette);
			}
			
			$objGroup = $this->Database->execute("SELECT * FROM tl_lead_groups WHERE id={$objForm->leadGroup}");
			$arrIds = deserialize($objGroup->fields);
			$arrIds = (is_array($arrIds) && count($arrIds)) ? $arrIds : array(0);
			
			$arrFields = array();
			$objFields = $this->Database->execute("SELECT * FROM tl_lead_fields WHERE id IN (".implode(',', $arrIds).") ORDER BY id=" . implode(" DESC, id=", $arrIds) . " DESC");
			
			while( $objFields->next() )
			{
				$arrFields[$objFields->field_name] = $objFields->name;
			}
			
			$GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
			$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadField']['options'] = $arrFields;
		}

	}
	
	
	public function validateFieldSelect($varValue, $dc)
	{
		if ($varValue != '')
		{
			$objFields = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_form_field WHERE pid=? AND leadField=? GROUP BY pid")->execute($dc->activeRecord->pid, $varValue);
			
			if ($objFields->total > 1)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $GLOBALS['TL_LANG']['tl_form_field'][$dc->field][0]));
			}
		}
		
		return $varValue;
	}
	
	
	public function processFormData($arrPost, $arrForm, $arrFiles)
	{
		if ($arrForm['leadGroup'] > 0)
		{
			$arrSet = array();
			$objFields = $this->Database->execute("SELECT * FROM tl_form_field WHERE pid={$arrForm['id']} AND leadField!=''");
			
			while( $objFields->next() )
			{
				if (isset($arrPost[$objFields->name]))
				{
					$arrSet[$objFields->leadField] = $arrPost[$objFields->name];
				}
			}
			
			if (count($arrSet))
			{
				$arrSet['tstamp'] = time();
				$arrSet['created'] = $arrSet['tstamp'];
				$arrSet['form_id'] = $arrForm['id'];
				$arrSet['group_id'] = $arrForm['leadGroup'];
				
				$this->Database->prepare("INSERT INTO tl_leads %s")->set($arrSet)->execute();
			}
		}
	}
	
	
	public function exportToCSV($dc)
	{
		$this->loadDataContainer('tl_leads');
		$arrWhere = array();
		$arrSession = $_SESSION['BE_DATA']['filter']['tl_leads'];

		// if we have a filter on the group, we only load the fields from the group
		if ($arrSession['group_id'] != '')
		{
			$objGroup = $this->Database->prepare('SELECT fields FROM tl_lead_groups WHERE id=?')
									   ->limit(1)
									   ->execute($arrSession['group_id']);

			$objFields = $this->Database->query('SELECT field_name FROM tl_lead_fields WHERE FIND_IN_SET(id, "' . implode(',', deserialize($objGroup->fields)). '")');
			$arrFields = $objFields->fetchEach('field_name');
			$strFields = implode(',', $arrFields);
		}
		else
		{
			$arrFields = array_keys($GLOBALS['TL_DCA']['tl_leads']['fields']);
			$strFields = implode(',', $arrFields);
		}
		
		$strQuery = 'SELECT ' . $strFields . ' FROM tl_leads';

		if ($arrSession['group_id'] != '')
		{
			$arrWhere[] = 'group_id = ' . $arrSession['group_id'];
		}

		if ($arrSession['form_id'] != '')
		{
			$arrWhere[] = 'form_id = ' . $arrSession['form_id'];
		}

		// add all where parts
		if (count($arrWhere) > 0)
		{
			$strQuery .= ' WHERE ' . implode(' AND ', $arrWhere);
		}

		$objExport = $this->Database->query($strQuery);
		
		header('Content-Type: text/plain, charset=UTF-16LE; encoding=UTF-16LE');
		header("Content-Disposition: attachment; filename=leads.csv");

		foreach ($objExport->fetchAllAssoc() as $arrRow)
		{
			foreach ($arrRow as $k=>$v)
			{
				// handle rgxp datim
				if ($GLOBALS['TL_DCA']['tl_leads']['fields'][$k]['eval']['rgxp'] == 'datim')
				{
					$arrRow[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $v);
				}

				// handle rgxp date
				if ($GLOBALS['TL_DCA']['tl_leads']['fields'][$k]['eval']['rgxp'] == 'date')
				{
					$arrRow[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $v);
				}
			}

			array_walk($arrRow, array($this, 'escapeRow'));
			$strCSV .= '"' . implode('"' . ';' . '"', $arrRow) . '"'.  ';' . "\n";
		}

		echo chr(255).chr(254).mb_convert_encoding($strCSV, 'UTF-16LE', 'UTF-8');
		exit;


	}

	/**
	 * Escape an entry
	 * 
	 * @param reference $varValue
	 * @return reference
	 */
	protected function escapeRow(&$varValue)
	{
		$varValue = str_replace('"', '""', $varValue);
	}
}

