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
	'eval'				=> array('tl_class'=>'w50', 'includeBlankOption'=>true, 'blankOptionLabel'=>'Feld nicht speichern'),
	'save_callback'		=> array
	(
//		array('Leads', 'validateFieldSelect'),
	),
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
		if ($this->Input->get('act') != 'edit')
		{
			return;
		}

		$objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id={$dc->id})");

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
}

