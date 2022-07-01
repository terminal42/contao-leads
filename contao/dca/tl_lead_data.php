<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @see       http://github.com/terminal42/contao-leads
 */

/**
 * Table tl_lead_data
 */
$GLOBALS['TL_DCA']['tl_lead_data'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'ptable'                    => 'tl_lead',
        'closed'                    => true,
        'notEditable'               => true,
        'notCopyable'               => true,
        'notSortable'               => true,
        'notDeletable'              => true,
        'onload_callback' => array
        (
            array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadDataListener::class, 'onLoadCallback')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'         => 'primary',
                'pid'        => 'index',
                'master_id'  => 'index',
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 4,
            'fields'                => array('sorting'),
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit',
            'headerFields'          => array('created', 'form_id'),
            'child_record_callback' => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadDataListener::class, 'onChildRecordCallback'),
            'disableGrouping'       => true,
        ),
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ),
        'pid' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'              => ['table' => 'tl_lead', 'type' => 'belongsTo'],
        ),
        'tstamp' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'sorting' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'master_id' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'              => ['table' => 'tl_form_field', 'type' => 'hasOne'],
        ),
        'field_id' => array
        (
            'sql'                   => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'              => ['table' => 'tl_form_field', 'type' => 'hasOne'],
        ),
        'name' => array
        (
            'sql'                   => ['type' => 'string', 'length' => 64, 'default' => ''],
        ),
        'value' => array
        (
            'sql'                   => ['type' => 'text', 'length' => \Doctrine\DBAL\Platforms\MySqlPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ),
        'label' => array
        (
            'sql'                   => ['type' => 'text', 'length' => \Doctrine\DBAL\Platforms\MySqlPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ),
    )
);
