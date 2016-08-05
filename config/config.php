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
 * Add the tl_lead_export table to form module
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_lead_export';

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
        'notification'  => array('tl_lead', 'sendNotification'),
    ),
)));

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'system/modules/leads/assets/leads.min.css';
}


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadLanguageFile'][]  = array('Leads\\Leads', 'loadLeadName');
$GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('Leads\\Leads', 'loadBackendModules');
$GLOBALS['TL_HOOKS']['processFormData'][]   = array('Leads\\Leads', 'processFormData');
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Leads\\Leads', 'handleSystemColumnExports');
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Leads\\Leads', 'handleTokenExports');

/**
 * Leads export types
 */
$GLOBALS['LEADS_EXPORT'] = array
(
    'csv'   => 'Leads\\Exporter\\Csv',
    'xls'   => 'Leads\\Exporter\\Xls',
    'xlsx'  => 'Leads\\Exporter\\Xlsx',
);

/**
 * Data transformers
 */
$GLOBALS['LEADS_DATA_TRANSFORMERS'] = array
(
    'raw'               => 'Leads\\DataTransformer\\RawTransformer',
    'date'              => 'Leads\\DataTransformer\\DateTransformer',
    'datim'             => 'Leads\\DataTransformer\\DatimTransformer',
    'time'              => 'Leads\\DataTransformer\\TimeTransformer',
    'yesno'             => 'Leads\\DataTransformer\\YesNoTransformer',
    'uuidToFilePath'    => 'Leads\\DataTransformer\\UuidToFilePathTransformer',
);
