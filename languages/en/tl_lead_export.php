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
 * Load forms language
 */
\System::loadLanguageFile('tl_form');
\System::loadLanguageFile('tl_form_field');


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name']           = array('Config name', 'Please enter the config name.');
$GLOBALS['TL_LANG']['tl_lead_export']['type']           = array('Type', 'Please enter the export type.');
$GLOBALS['TL_LANG']['tl_lead_export']['headerFields']   = array('Header fields', 'Include the header fields in the file.');
$GLOBALS['TL_LANG']['tl_lead_export']['export']         = array('Export type', 'Please choose what data should be exported.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields']         = array('Fields', 'Please choose the fields you want to export.');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_field']   = array('Field');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_name']    = array('Header name');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']   = array('Value');
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']  = array('Format');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_lead_export']['name_legend']   = 'Name and type';
$GLOBALS['TL_LANG']['tl_lead_export']['config_legend'] = 'Configuration';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_lead_export']['export']['all']          = 'Export all data';
$GLOBALS['TL_LANG']['tl_lead_export']['export']['fields']       = 'Custom export';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['all']    = 'Label and value';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['label']  = 'Label only (if available, fallback to value)';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_value']['value']  = 'Value only';
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['raw']   = &$GLOBALS['TL_LANG']['tl_form']['raw'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['date']  = &$GLOBALS['TL_LANG']['tl_form_field']['date'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['datim'] = &$GLOBALS['TL_LANG']['tl_form_field']['datim'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['fields_format']['time']  = &$GLOBALS['TL_LANG']['tl_form_field']['time'][0];
$GLOBALS['TL_LANG']['tl_lead_export']['field_form']             = 'Form';
$GLOBALS['TL_LANG']['tl_lead_export']['field_created']          = 'Date created';
$GLOBALS['TL_LANG']['tl_lead_export']['field_member']           = 'Member';


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
