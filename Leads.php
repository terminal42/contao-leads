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


class Leads extends Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->import('Database');

		if (FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');
		}
	}


	public function loadBackendModules($arrModules, $blnShowAll)
	{
		$objForms = $this->Database->execute("SELECT *, IF(leadMenuLabel='', title, leadMenuLabel) AS leadMenuLabel FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 ORDER BY leadMenuLabel");

		if ($objForms->numRows)
		{
			array_insert($arrModules, 1, array('leads' => array
			(
				'icon'	=> 'modMinus.gif',
				'title'	=> 'Bereich schließen',
				'label'	=> 'Leads',
				'href'	=> 'contao/main.php?do=leads&amp;master=2&amp;mtg=leads',
				'modules' => array(),
			)));

			while ($objForms->next())
			{
				$arrModules['leads']['modules']['leads_'.$objForms->id] = array
				(
					'tables'	=> array(),
					'title'		=> specialchars('Leads für Formular "' . $objForms->title . '" verwalten.'),
	                'label'		=> $objForms->leadMenuLabel,
	                'icon'		=> 'style="background-image:url(\'system/modules/leads/assets/icon.png\')"',
	                'class'		=> 'navigation leads',
	                'href'		=> 'contao/main.php?do=leads&master='.$objForms->id,
				);
			}
		}

		return $arrModules;
	}


	public function processFormData($arrPost, $arrForm, $arrFiles)
	{

	}
}

