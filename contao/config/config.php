<?php

use Contao\ArrayUtil;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, [
    'leads' => [
        'lead' => [
            'tables' => ['tl_lead', 'tl_lead_data'],
        ],
    ],
]);
