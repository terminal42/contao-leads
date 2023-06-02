<?php

use Contao\ArrayUtil;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, [
    'leads' => [
        'lead' => [
            'tables' => ['tl_lead', 'tl_lead_data'],
        ],
    ],
]);

$GLOBALS['BE_MOD']['content']['form']['tables'][] = 'tl_lead_export';

$GLOBALS['TL_PERMISSIONS'][] = 'leadp';
