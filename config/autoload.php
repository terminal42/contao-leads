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
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'Leads'       => 'system/modules/leads/Leads.php',
    'LeadsExport' => 'system/modules/leads/LeadsExport.php',
));
