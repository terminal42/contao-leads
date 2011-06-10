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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['name']					= array('Name', 'Please enter a name for this attribute.');
$GLOBALS['TL_LANG']['tl_lead_fields']['field_name']				= array('Internal name', 'Internal name is the database field name and must be unique.');
$GLOBALS['TL_LANG']['tl_lead_fields']['type']					= array('Type', 'Please select a type for this field.');
$GLOBALS['TL_LANG']['tl_lead_fields']['description']			= array('Description', 'The description is shown as a hint to the backend user.');
$GLOBALS['TL_LANG']['tl_lead_fields']['options']				= array('Options', 'Please enter one or more options. Use the buttons to add, move or delete an option. If you are working without JavaScript assistance, you should save your changes before you modify the order!');
$GLOBALS['TL_LANG']['tl_lead_fields']['mandatory']				= array('Mandatory field', 'This field cannot be empty.');
$GLOBALS['TL_LANG']['tl_lead_fields']['multiple']				= array('Multiple selection', 'Allow to select more than one option.');
$GLOBALS['TL_LANG']['tl_lead_fields']['size']					= array('List size', 'Here you can enter the size of the select box.');
$GLOBALS['TL_LANG']['tl_lead_fields']['extensions']				= array('Allowed file types', 'A comma separated list of valid file extensions.');
$GLOBALS['TL_LANG']['tl_lead_fields']['rte']					= array('Use HTML Editor', 'Select a tinyMCE configuration file to enable the rich text editor.');
$GLOBALS['TL_LANG']['tl_lead_fields']['rgxp']					= array('Input validation', 'Validate the input against a regular expression.');
$GLOBALS['TL_LANG']['tl_lead_fields']['maxlength']				= array('Maximum length', 'Limit the field length to a certain number of characters (text) or bytes (file uploads).');
$GLOBALS['TL_LANG']['tl_lead_fields']['foreignKey']				= array('Foreign Table & Field', 'Instead of adding options you can enter a table.field combination to select from database.');
$GLOBALS['TL_LANG']['tl_lead_fields']['filter']   				= array('Filterable', 'Can this attribute be used in a backend filter?');
$GLOBALS['TL_LANG']['tl_lead_fields']['search']		   			= array('Searchable', 'Should the search engine look in this field for search terms?');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['new']			= array('Add field', 'Create a new lead field');
$GLOBALS['TL_LANG']['tl_lead_fields']['edit']			= array('Edit field', 'Edit field ID %s');
$GLOBALS['TL_LANG']['tl_lead_fields']['delete']			= array('Delete field', 'Delete field ID %s');
$GLOBALS['TL_LANG']['tl_lead_fields']['show']			= array('Field details', 'Show details of field ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_lead_fields']['field_legend']		= 'Field';
$GLOBALS['TL_LANG']['tl_lead_fields']['description_legend']	= 'Description';
$GLOBALS['TL_LANG']['tl_lead_fields']['options_legend']		= 'Options';
$GLOBALS['TL_LANG']['tl_lead_fields']['config_legend']		= 'Configuration';

