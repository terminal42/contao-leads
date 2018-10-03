<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
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
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'master_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'field_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'name' => array
        (
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'value' => array
        (
            'sql'                     => "text NULL"
        ),
        'label' => array
        (
            'sql'                     => "text NULL"
        ),
    )
);
