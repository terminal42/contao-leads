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
 * Fake back end module
 */
array_insert($GLOBALS['BE_MOD'], 1, array('leads'=> array
(
    'lead' => array
    (
        'tables'        => array('tl_lead', 'tl_lead_data'),
        'javascript'    => 'system/modules/leads/assets/leads.min.js',
        'stylesheet'    => 'system/modules/leads/assets/leads.min.css',
        'show'          => array('tl_lead', 'show'),
        'export'        => array('tl_lead', 'export'),
    ),
)));

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadLanguageFile'][]  = array('Leads', 'loadLeadName');
$GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('Leads', 'loadBackendModules');
$GLOBALS['TL_HOOKS']['processFormData'][]   = array('Leads', 'processFormData');
