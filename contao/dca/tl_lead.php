<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;

$GLOBALS['TL_DCA']['tl_lead'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'closed' => true,
        'notEditable' => true,
        'ctable' => ['tl_lead_data'],
        'permissions' => ['delete'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'main_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['created DESC'],
            'flag' => DataContainer::SORT_MONTH_DESC,
            'panelLayout' => 'filter;data_search,sort,limit',
        ],
        'label' => [
            'fields' => ['created'],
            'format' => &$GLOBALS['TL_LANG']['tl_lead']['label_format'],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'main_id' => [
            'foreignKey' => "tl_form.CONCAT(title, ' (ID ', id, ')')",
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_form', 'type' => 'hasOne'],
        ],
        'form_id' => [
            'filter' => true,
            'sorting' => true,
            'foreignKey' => "tl_form.CONCAT(title, ' (ID ', id, ')')",
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_form', 'type' => 'hasOne'],
        ],
        'language' => [
            'filter' => true,
            'sorting' => true,
            'sql' => ['type' => 'string', 'length' => 5, 'default' => ''],
        ],
        'created' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_MONTH_DESC,
            'eval' => ['rgxp' => 'datim'],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'member_id' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_DESC,
            'foreignKey' => "tl_member.CONCAT(lastname, ' ', firstname)",
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['table' => 'tl_member', 'type' => 'hasOne'],
        ],
        'post_data' => [
            'eval' => ['doNotShow' => true],
            'sql' => [
                'type' => 'blob',
                'length' => AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMBLOB,
                'notnull' => false,
            ],
        ],
    ],
];
