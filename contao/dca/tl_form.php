<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadEnabled,leadMain'] = 'index';

PaletteManipulator::create()
    ->addField('leadEnabled', 'storeValues')
    ->applyToPalette('default', 'tl_form')
;

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadEnabled';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['leadEnabled'] = 'leadMain';

$GLOBALS['TL_DCA']['tl_form']['fields']['leadEnabled'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMain'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => [
        'submitOnChange' => true,
        'includeBlankOption' => true,
        'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_form']['leadMain'][2],
        'tl_class' => 'w50',
    ],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMenuLabel'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50', 'decodeEntities' => true],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['leadLabel'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => [
        'mandatory' => true,
        'decodeEntities' => true,
        'allowHtml' => true,
        'tl_class' => 'clr',
    ],
    'sql' => 'text NULL',
];
