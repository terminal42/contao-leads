<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('leadp', 'formp')
    ->applyToPalette('default', 'tl_user_group')
;

$GLOBALS['TL_DCA']['tl_user_group']['fields']['leadp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['edit', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user_group']['leadp'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];
