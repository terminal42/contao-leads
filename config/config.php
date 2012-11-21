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
 * Fake back end module
 */
array_insert($GLOBALS['BE_MOD'], 1, array('leads'=> array
(
	'lead' => array
	(
		'tables'		=> array('tl_lead', 'tl_lead_data'),
		'javascript'	=> 'system/modules/leads/assets/leads.min.js',
		'stylesheet'	=> 'system/modules/leads/assets/leads.min.css',
		'show'			=> array('tl_lead', 'show'),
		'export'		=> array('tl_lead', 'export'),
	),
)));


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadLanguageFile'][] = array('Leads', 'loadLeadName');
$GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('Leads', 'loadBackendModules');
$GLOBALS['TL_HOOKS']['processFormData'][] = array('Leads', 'processFormData');

