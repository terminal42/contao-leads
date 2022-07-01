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
 * Config
 */
$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = array(Terminal42\LeadsBundle\EventListener\DataContainer\FormFieldListener::class, 'onLoadCallback');

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['leadStore'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array(Terminal42\LeadsBundle\EventListener\DataContainer\FormFieldListener::class, 'onLeadStoreOptions'),
    'eval'                  => array('tl_class'=>'w50', 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form_field']['leadStoreSelect'][2]),
    'sql'                   => ['type' => 'string', 'length' => 10, 'default' => ''],
);
