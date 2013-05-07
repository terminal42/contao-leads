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
 * Config
 */
$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = array('tl_form_field_leads', 'injectFieldSelect');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreSelect'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form_field']['leadStoreSelect'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('tl_form_field_leads', 'getMasterFields'),
	'eval'				=> array('tl_class'=>'w50', 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form_field']['leadStoreSelect'][2]),
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreCheckbox'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form_field']['leadStoreCheckbox'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'eval'				=> array('tl_class'=>'w50 m12'),
);


class tl_form_field_leads extends Backend
{

	public function injectFieldSelect($dc)
	{
		$intId = 0;
		switch ($this->Input->get('act'))
		{
			case 'edit':
				$intId = $this->Database->execute("SELECT pid FROM tl_form_field WHERE id=$dc->id")->pid;
				break;
			case 'editAll':
			case 'overrideAll':
				$intId = $this->Input->get('id');
				break;
		}

		if ($intId === 0)
		{
			return;
		}

		$objForm = $this->Database->execute("SELECT leadEnabled,leadMaster FROM tl_form WHERE id=$intId");

		if ($objForm->leadEnabled)
		{
			$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore'] = ($objForm->leadMaster ? $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreSelect'] : $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreCheckbox']);

			foreach( $GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $strName => $strPalette )
			{
				if (in_array($strName, array('__selector__', 'submit', 'default', 'headline', 'explanation')))
				{
					continue;
				}

				$GLOBALS['TL_DCA']['tl_form_field']['palettes'][$strName] = str_replace(',type,', ',type,leadStore,', $strPalette);
			}

			$GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
		}
	}


	public function getMasterFields($dc)
	{
		$arrFields = array();
		$objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id={$dc->id})");

		if ($objForm->leadEnabled && $objForm->leadMaster > 0)
		{
			$objFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE name!='' AND pid=? AND leadStore='1' AND id NOT IN (SELECT leadStore FROM tl_form_field WHERE pid=? AND id!=?) ORDER BY sorting")->execute($objForm->leadMaster, $objForm->id, $dc->activeRecord->id);

			while ($objFields->next())
			{
				$arrFields[$objFields->id] = $objFields->label == '' ? $objFields->name : ($objFields->label . ' (' . $objFields->name . ')');
			}
		}

		return $arrFields;
	}
}

