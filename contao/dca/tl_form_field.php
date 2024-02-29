<?php

$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'leadEnabled';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['leadStore'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];
