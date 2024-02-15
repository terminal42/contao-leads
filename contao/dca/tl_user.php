<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('leadp', 'formp')
    ->applyToPalette('default', 'tl_user')
;

$GLOBALS['TL_DCA']['tl_user']['fields']['leadp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['edit', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['leadp'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];
