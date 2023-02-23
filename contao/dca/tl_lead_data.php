<?php

$GLOBALS['TL_DCA']['tl_lead_data'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'ptable' => 'tl_lead',
        'notCreatable' => true,
        'notCopyable' => true,
        'notSortable' => true,
        'notDeletable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'main_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['created', 'form_id'],
        ],
        'label' => [
            'fields' => ['name', 'value', 'label'],
            'format' => '<div style="display:flex"><div style="width:20%%;margin-right:10px;font-weight:500">%s</div><div style="width:50%%;margin-right:10px">%s</div><div style="width:20%%;color:#b3b3b3;">%s</div></div>',
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => 'value,label',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_lead', 'type' => 'belongsTo'],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'sorting' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'main_id' => [
            'foreignKey' => "tl_form_field.CONCAT(name, ' (ID ', id, ')')",
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_form_field', 'type' => 'hasOne'],
        ],
        'field_id' => [
            'foreignKey' => "tl_form_field.CONCAT(name, ' (ID ', id, ')')",
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_form_field', 'type' => 'hasOne'],
        ],
        'name' => [
            'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'value' => [
            'inputType' => 'textarea',
            'sql' => [
                'type' => 'text',
                'length' => \Doctrine\DBAL\Platforms\AbstractMySQLPlatform::LENGTH_LIMIT_TEXT,
                'notnull' => false,
            ],
        ],
        'label' => [
            'inputType' => 'textarea',
            'sql' => [
                'type' => 'text',
                'length' => \Doctrine\DBAL\Platforms\AbstractMySQLPlatform::LENGTH_LIMIT_TEXT,
                'notnull' => false,
            ],
        ],
    ],
];
