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
 * Table tl_lead
 */
$GLOBALS['TL_DCA']['tl_lead'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'         => 'Table',
        'enableVersioning'      => true,
        'closed'                => true,
        'notEditable'           => true,
        'ctable'                => array('tl_lead_data'),
        'onload_callback' => array
        (
            array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadListener::class, 'onLoadCallback'),
            array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportOperationListener::class, 'onLoadCallback'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'        => 'primary',
                'master_id' => 'index',
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => array('created DESC', 'member_id'),
            'flag'              => 8,
            'panelLayout'       => 'filter;sort,limit',
            'filter'            => array(array('master_id=?', $this->Input->get('master'))),
        ),
        'label' => array
        (
            'fields'            => array('created'),
            'format'            => &$GLOBALS['TL_LANG']['tl_lead']['label_format'],
            'label_callback'    => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadListener::class, 'onLabelCallback'),
        ),
        'global_operations' => array
        (
            'export_config' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_lead']['export_config'],
                'icon'            => 'settings.gif',
                'class'           => 'leads-export',
                'attributes'      => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadListener::class, 'onExportButtonCallback'),
            ),
            'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ),
        ),
        'operations' => array
        (
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['show'],
                'href'          => '',
                'icon'          => 'show.gif',
                'button_callback' => [\Terminal42\LeadsBundle\EventListener\DataContainer\LeadListener::class, 'onShowButtonCallback'],
            ),
            'data' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['data'],
                'href'          => 'table=tl_lead_data',
                'icon'          => 'bundles/terminal42leads/field.png',
            ),
        ),
    ),

    // Select
    'select' => array
    (
        'buttons_callback' => array
        (
            array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadListener::class, 'onSelectButtonsCallback'),
        ),
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ),
        'tstamp' => array
        (
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'master_id' => array
        (
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'          => ['table' => 'tl_form', 'type' => 'hasOne'],
        ),
        'form_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['form_id'],
            'filter'            => true,
            'sorting'           => true,
            'foreignKey'        => 'tl_form.title',
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'          => ['table' => 'tl_form', 'type' => 'hasOne'],
        ),
        'language' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['language'],
            'filter'            => true,
            'sorting'           => true,
            'options'           => \System::getLanguages(),
            'sql'               => ['type' => 'string', 'length' => 5, 'default' => ''],
        ),
        'created' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['created'],
            'sorting'           => true,
            'flag'              => 8,
            'eval'              => array('rgxp'=>'datim'),
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['member'],
            'filter'            => true,
            'sorting'           => true,
            'flag'              => 12,
            'foreignKey'        => "tl_member.CONCAT(lastname, ' ', firstname)",
            'sql'               => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'          => ['table' => 'tl_member', 'type' => 'hasOne'],
        ),
        'post_data' => array
        (
            'sql'               => ['type' => 'blob', 'length' => \Doctrine\DBAL\Platforms\MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB, 'notnull' => false],
        ),
    ),
);
