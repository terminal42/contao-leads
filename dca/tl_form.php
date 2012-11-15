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
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = array('tl_form_lead', 'modifyPalette');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['leadEnabled'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['leadEnabled'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'eval'				=> array('tl_class'=>'clr', 'submitOnChange'=>true),
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMaster'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['leadMaster'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('tl_form_lead', 'getMasterForms'),
	'eval'				=> array('submitOnChange'=>true, 'includeBlankOption'=>true, 'blankOptionLabel'=>'Dies ist ein Master-Formular', 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMenuLabel'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'],
	'exclude'			=> true,
	'inputType'			=> 'text',
	'eval'				=> array('maxlength'=>32, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadLabel'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['leadLabel'],
	'exclude'			=> true,
	'inputType'			=> 'textarea',
	'eval'				=> array('mandatory'=>true, 'decodeEntities'=>true, 'style'=>'height:60px', 'allowHtml'=>true, 'tl_class'=>'clr'),
);


class tl_form_lead extends Backend
{

	/**
	 * Modify the palette based on configuration. We can't use simple subpalettes because we do complex things...
	 * @param DataContainer
	 */
	public function modifyPalette($dc)
	{
		$strPalette = 'leadEnabled';
		$objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=" . (int) $dc->id);

		if ($objForm->leadEnabled)
		{
			$strPalette .= ',leadMaster';

			if ($objForm->leadMaster == 0)
			{
				$strPalette .= ',leadMenuLabel,leadLabel';
			}
		}

		$GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace('storeValues', 'storeValues,'.$strPalette, $GLOBALS['TL_DCA']['tl_form']['palettes']['default']);
		$GLOBALS['TL_DCA']['tl_form']['subpalettes']['leadEnabled'] = 'leadMaster,leadMenuLabel,leadLabel';

	}


	public function getMasterForms($dc)
	{
		$arrForms = array();
		$objForms = $this->Database->execute("SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 AND id!=" . (int) $dc->id);

		while ($objForms->next())
		{
			$arrForms[$objForms->id] = $objForms->title;
		}

		return $arrForms;
	}
}

