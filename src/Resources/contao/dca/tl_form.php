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
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_lead_export';
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = array(Terminal42\LeadsBundle\EventListener\DataContainer\FormListener::class, 'onLoadCallback');
$GLOBALS['TL_DCA']['tl_form']['config']['oncopy_callback'][] = array(Terminal42\LeadsBundle\EventListener\DataContainer\FormListener::class, 'onCopyCallback');
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadEnabled'] = 'index';
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadMaster'] = 'index';
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadEnabled,leadMaster'] = 'index';
// TODO fix indexes

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadEnabled';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['leadEnabled'] = 'leadMaster';

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField('leadEnabled', 'storeValues')
    ->applyToPalette('default', 'tl_form')
;

/**
 * Fields
 */
// TODO use array for SQL definition
$GLOBALS['TL_DCA']['tl_form']['fields']['leadEnabled'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadEnabled'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => array('tl_class'=>'clr', 'submitOnChange'=>true),
    'sql'                   => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMaster'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMaster'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array(Terminal42\LeadsBundle\EventListener\DataContainer\FormListener::class, 'onLeadMasterOptions'),
    'eval'                  => array('submitOnChange'=>true, 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'], 'tl_class'=>'w50'),
    'sql'                   => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMenuLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                   => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadLabel'],
    'exclude'               => true,
    'inputType'             => 'textarea',
    'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'style'=>'height:60px', 'allowHtml'=>true, 'tl_class'=>'clr'),
    'sql'                   => 'text NULL'
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadPeriod'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadPeriod'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>false, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50'),
    'sql'                   => "int(10) unsigned NOT NULL default '0'"
);
