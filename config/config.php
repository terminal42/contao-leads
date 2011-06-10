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
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['leads'] = array
(
	'tables'			=> array('tl_leads', 'tl_lead_groups', 'tl_lead_fields'),
	'icon'				=> 'system/modules/leads/html/icon.png',
	'stylesheet'		=> 'system/modules/leads/html/style.css',
);


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Leads', 'loadFields');
$GLOBALS['TL_HOOKS']['processFormData'][] = array('Leads', 'processFormData');


/**
 * Lead field types
 */
$GLOBALS['LEAD_FFL'] = array
(
	'text' => array
	(
		'sql'		=> "varchar(255) NOT NULL default ''",
	),
	'textarea' => array
	(
		'sql'		=> "text NULL",
	),
	'select' => array
	(
		'sql'		=> "blob NULL",
	),
	'radio' => array
	(
		'sql'		=> "blob NULL",
	),
	'checkbox' => array
	(
		'sql'		=> "blob NULL",
	),
);
