<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_form']['leadEnabled']   = array('Store leads', 'Store submitted data from this form as leads.');
$GLOBALS['TL_LANG']['tl_form']['leadMaster']    = array('Master configuration', 'Select if this form is a master or slave configuration.');
$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'] = array('Navigation label', 'Enter a custom label for the backend navigation. If you leave this field blank, the form name will be used.');
$GLOBALS['TL_LANG']['tl_form']['leadLabel']     = array('Record label', 'Enter the names of the fields to be displayed in the back end list, surrounded by double hashes (##fieldname##). You can also use plain text. Use ##created## to output the date and time of creation.');

/**
 * Other
 */
$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'] = 'This is a master form';
