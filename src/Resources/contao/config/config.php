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
        'javascript'    => 'bundles/terminal42leads/leads.min.js',
        'stylesheet'    => 'bundles/terminal42leads/leads.min.css',
        'show'          => array('tl_lead', 'show'),
        'export'        => array('tl_lead', 'export'),
        'notification'  => array('tl_lead', 'sendNotification'),
    ),
)));

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/terminal42leads/leads.min.css';
}

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadLanguageFile'][]  = array('Terminal42\LeadsBundle\Leads', 'loadLeadName');
$GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('Terminal42\LeadsBundle\Leads', 'loadBackendModules');
$GLOBALS['TL_HOOKS']['processFormData'][]   = array('Terminal42\LeadsBundle\Leads', 'processFormData');
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Terminal42\LeadsBundle\Leads', 'handleSystemColumnExports');
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Terminal42\LeadsBundle\Leads', 'handleTokenExports');

/**
 * Leads export types
 */
$GLOBALS['LEADS_EXPORT'] = array
(
    'csv'   => 'Terminal42\LeadsBundle\Exporter\Csv',
    'xls'   => 'Terminal42\LeadsBundle\Exporter\Xls',
    'xlsx'  => 'Terminal42\LeadsBundle\Exporter\Xlsx',
);

/**
 * Data transformers
 */
$GLOBALS['LEADS_DATA_TRANSFORMERS'] = array
(
    'raw'               => 'Terminal42\LeadsBundle\DataTransformer\RawTransformer',
    'date'              => 'Terminal42\LeadsBundle\DataTransformer\DateTransformer',
    'datim'             => 'Terminal42\LeadsBundle\DataTransformer\DatimTransformer',
    'time'              => 'Terminal42\LeadsBundle\DataTransformer\TimeTransformer',
    'yesno'             => 'Terminal42\LeadsBundle\DataTransformer\YesNoTransformer',
    'uuidToFilePath'    => 'Terminal42\LeadsBundle\DataTransformer\UuidToFilePathTransformer',
);
