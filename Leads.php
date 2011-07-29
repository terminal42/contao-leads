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
			$this->loadDataContainer('tl_leads');
			
			$arrSet = array();
			$objFields = $this->Database->execute("SELECT * FROM tl_form_field WHERE pid={$arrForm['id']} AND leadField!=''");
			
			while( $objFields->next() )
			{
				if (isset($arrPost[$objFields->name]))
				{
					$varValue = $arrPost[$objFields->name];
					
					// Convert date formats into timestamps
					if ($varValue != '' && in_array($GLOBALS['TL_DCA']['tl_leads']['fields'][$objFields->leadField]['eval']['rgxp'], array('date', 'time', 'datim')))
					{
						$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$GLOBALS['TL_DCA']['tl_leads']['fields'][$objFields->leadField]['eval']['rgxp'] . 'Format']);
						$varValue = $objDate->tstamp;
					}
					
					$arrSet[$objFields->leadField] = $varValue;
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
	
	
	public function exportToCSV($dc, $strTable, $arrModule, $blnExcel=false)
	{
		$arrSession = $_SESSION['BE_DATA']['filter']['tl_leads'];
		$arrWhere = array();
		$arrValues = array();

		$arrFields = array_keys($GLOBALS['TL_DCA']['tl_leads']['fields']);

		// if we have a filter on the group, we only load the fields from the group
		if ($arrSession['group_id'] != '')
		{
			$arrWhere[] = 'group_id=?';
			$arrValues[] = $arrSession['group_id'];
			
			$objGroup = $this->Database->prepare('SELECT fields FROM tl_lead_groups WHERE id=?')
									   ->limit(1)
									   ->execute($arrSession['group_id']);

			$arrGroupFields = deserialize($objGroup->fields);
			
			if (is_array($arrGroupFields) && count($arrGroupFields))
			{
				$objGroupFields = $this->Database->query('SELECT field_name FROM tl_lead_fields WHERE id IN(' . implode(',', $arrGroupFields). ')');
				$arrFields = array_merge(array('created','tstamp','group_id','form_id'), $objFields->fetchEach('field_name'));
			}
		}

		if ($arrSession['form_id'] != '')
		{
			$arrWhere[] = 'form_id=?';
			$arrValues[] = $arrSession['form_id'];
		}

		$objExport = $this->Database->prepare("SELECT " . implode(',', $arrFields) . " FROM tl_leads" . (count($arrWhere) > 0 ? (' WHERE ' . implode(' AND ', $arrWhere)) : ''))->execute($arrValues);

		header('Content-Type: text/csv, charset=UTF-16LE; encoding=UTF-16LE');
		header('Content-Disposition: attachment; filename=leads_'.date('Ymd').'.csv');

		// add the header fields
		foreach ($arrFields as $field)
		{
			$arrLabels[] = $GLOBALS['TL_DCA']['tl_leads']['fields'][$field]['label'][0] ? $GLOBALS['TL_DCA']['tl_leads']['fields'][$field]['label'][0] : $field;
		}
		
		$strSeparator = $blnExcel ? "\t" : ',';

		array_walk($arrLabels, array($this, 'escapeRow'));
		$strCSV .= '"' . implode('"' . $strSeparator . '"', $arrLabels) . '"'.  "\n";

		foreach( $objExport->fetchAllAssoc() as $arrRow )
		{
			foreach ( $arrRow as $k => $v )
			{
				$arrRow[$k] = $this->formatValue($k, $v);
			}

			array_walk($arrRow, array($this, 'escapeRow'));
			$strCSV .= '"' . implode('"' . $strSeparator . '"', $arrRow) . '"'.  "\n";
		}

		if ($blnExcel)
		{
			echo chr(255).chr(254).mb_convert_encoding($strCSV, 'UTF-16LE', 'UTF-8');
		}
		else
		{
			echo $strCSV;
		}
		exit;
	}
	
	
	/**
	 * Export in Excel compatible CSV mode
	 */
	public function exportToExcel($dc, $strTable, $arrModule)
	{
		$this->exportToCSV($dc, $strTable, $arrModule, true);
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
	
	
	/**
	 * Format value (based on DC_Table::show(), Contao 2.9.0)
	 * @param  mixed
	 * @param  string
	 * @param  string
	 * @return string
	 */
	protected function formatValue($field, $value)
	{
		$table = 'tl_leads';
		$value = deserialize($value);

		// Get field value
		if (strlen($GLOBALS['TL_DCA'][$table]['fields'][$field]['foreignKey']))
		{
			$temp = array();
			$chunks = explode('.', $GLOBALS['TL_DCA'][$table]['fields'][$field]['foreignKey'], 2);

			$objKey = $this->Database->execute("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id IN (" . implode(',', array_map('intval', (array)$value)) . ")");

			return implode(', ', $objKey->fetchEach('value'));
		}

		elseif (is_array($value))
		{
			foreach ($value as $kk=>$vv)
			{
				if (is_array($vv))
				{
					$vals = array_values($vv);
					$value[$kk] = $vals[0].' ('.$vals[1].')';
				}
			}

			return implode(', ', $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'date')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'time')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10)) || $field == 'tstamp')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['multiple'])
		{
			return strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] == 'textarea' && ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['allowHtml'] || $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['preserveTags']))
		{
			return specialchars($value);
		}

		elseif (is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference']))
		{
			return isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value]) ? ((is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value])) ? $GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value][0] : $GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value]) : $value;
		}

		return $value;
	}
}

