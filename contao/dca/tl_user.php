<?php

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField('leadp', 'formp')
    ->applyToPalette('default', 'tl_user')
;

$GLOBALS['TL_DCA']['tl_user']['fields']['leadp'] = array
(
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'options'               => array('edit', 'delete'),
    'reference'             => &$GLOBALS['TL_LANG']['tl_user']['leadp'],
    'eval'                  => array('multiple'=>true, 'tl_class'=>'w50 w50h'),
    'sql'                   => 'blob NULL',
);
