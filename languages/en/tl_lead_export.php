<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name']           = array('Config name', 'Please enter the config name.');
$GLOBALS['TL_LANG']['tl_lead_export']['type']           = array('Type', 'Please enter the export type.');
$GLOBALS['TL_LANG']['tl_lead_export']['headerFields']   = array('Header fields', 'Include the header fields in the file.');
$GLOBALS['TL_LANG']['tl_lead_export']['includeFormId']  = array('Include form ID', 'Include the form ID in the file.');
$GLOBALS['TL_LANG']['tl_lead_export']['includeCreated'] = array('Include date created', 'Include the date created in the file.');
$GLOBALS['TL_LANG']['tl_lead_export']['includeMember']  = array('Include member ID', 'Include the member ID in the file.');
$GLOBALS['TL_LANG']['tl_lead_export']['limitFields']    = array('Limit fields', 'Limit the fields and their settings. By default all fields are exported.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields']         = array('Fields', 'Please choose the fields you want to export.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_field']   = array('Field');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_name']    = array('Name');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']   = array('Value');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']  = array('Format');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name_legend']   = 'Name and type';
$GLOBALS['TL_LANG']['tl_lead_export']['config_legend'] = 'Configuration';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_legend'] = 'Field settings';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['all']    = 'Label and value';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['label']  = 'Label only';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['value']  = 'Value only';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['raw']   = 'Raw data';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['date']  = 'Date';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['datim'] = 'Date and time';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['time']  = 'Time';


/**
 * Export types
 */
$GLOBALS['TL_LANG']['tl_lead_export']['type']['csv']  = 'CSV (.csv)';
$GLOBALS['TL_LANG']['tl_lead_export']['type']['xls']  = 'Excel 97/2000/2003 (.xls)';
$GLOBALS['TL_LANG']['tl_lead_export']['type']['xlsx'] = 'Excel 2007/2010 (.xlsx)';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_lead_export']['new']    = array('New config', 'Create a new config');
$GLOBALS['TL_LANG']['tl_lead_export']['show']   = array('Config details', 'Show the details of config ID %s');
$GLOBALS['TL_LANG']['tl_lead_export']['edit']   = array('Edit config', 'Edit config ID %s');
$GLOBALS['TL_LANG']['tl_lead_export']['cut']    = array('Move config', 'Move config ID %s');
$GLOBALS['TL_LANG']['tl_lead_export']['copy']   = array('Duplicate config', 'Duplicate config ID %s');
$GLOBALS['TL_LANG']['tl_lead_export']['delete'] = array('Delete config', 'Delete config ID %s');
