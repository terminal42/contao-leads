<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @see       http://github.com/terminal42/contao-leads
 */

/**
 * Add the tl_lead_export table to form module
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_lead_export';

/**
 * Fake back end module
 */
\Contao\ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, array('leads'=> array
(
    'lead' => array
    (
        'tables'        => array('tl_lead', 'tl_lead_data'),
        'javascript'    => \Contao\System::getContainer()->get('assets.packages')->getUrl('leads.js', 'terminal42_leads'),
        'export'        => array(Terminal42\LeadsBundle\Controller\Backend\LeadExportController::class, '__invoke'),
        'notification'  => array(Terminal42\LeadsBundle\Controller\Backend\LeadNotificationController::class, '__invoke'),
    ),
)));

// Load icon in Contao 4.2+ backend
if (defined('TL_MODE') && 'BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = \Contao\System::getContainer()->get('assets.packages')->getUrl('leads.css', 'terminal42_leads');
}

$GLOBALS['TL_MODELS']['tl_lead'] = \Terminal42\LeadsBundle\Model\Lead::class;
$GLOBALS['TL_MODELS']['tl_lead_data'] = \Terminal42\LeadsBundle\Model\LeadData::class;
$GLOBALS['TL_MODELS']['tl_lead_export'] = \Terminal42\LeadsBundle\Model\LeadExport::class;

// Cron jobs
$GLOBALS['TL_CRON']['daily']['purgeLeads'] = array('Terminal42\LeadsBundle\EventListener\CronjobListener', 'onDaily');

