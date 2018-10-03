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
        'javascript'    => 'bundles/terminal42leads/leads.js',
        'stylesheet'    => 'bundles/terminal42leads/leads.css',
        'show'          => array(Terminal42\LeadsBundle\Controller\Backend\LeadDetailsController::class, '__invoke'),
        'export'        => array(Terminal42\LeadsBundle\Controller\Backend\LeadExportController::class, '__invoke'),
        'notification'  => array(Terminal42\LeadsBundle\Controller\Backend\LeadNotificationController::class, '__invoke'),
    ),
)));

// Load icon in Contao 4.2+ backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/terminal42leads/leads.css';
}

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Terminal42\LeadsBundle\Leads', 'handleSystemColumnExports');
$GLOBALS['TL_HOOKS']['getLeadsExportRow'][] = array('Terminal42\LeadsBundle\Leads', 'handleTokenExports');

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
