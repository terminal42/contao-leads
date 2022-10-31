<?php

$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'leadEnabled';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['leadStore'],
    'exclude'               => true,
    'inputType'             => 'select',
    'eval'                  => array('tl_class'=>'w50', 'includeBlankOption'=>true),
    'sql'                   => ['type' => 'string', 'length' => 64, 'default' => ''],
);
