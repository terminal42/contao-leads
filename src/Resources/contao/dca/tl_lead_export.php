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
 * Table tl_lead_export
 */
$GLOBALS['TL_DCA']['tl_lead_export'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_form',
        'enableVersioning'            => true,
        'onload_callback' => array
        (
            array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onLoadCallback'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'        => 'primary',
                'pid'       => 'index',
                'cliExport' => 'index',
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('type', 'name'),
            'headerFields'            => array('title', 'tstamp', 'leadEnabled', 'leadMaster', 'leadMenuLabel', 'leadLabel'),
            'panelLayout'             => 'filter;search,limit',
            'child_record_callback'   => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onChildRecordCallback'),
            'child_record_class'      => 'no_padding'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_export']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_export']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif'
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_export']['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_export']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_export']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('type', 'useTemplate', 'export', 'cliExport'),
        'default'                     => '{name_legend},name,type,filename;{config_legend},export,cliExport;{date_legend:hide},lastRun,skipLastRun',
        'csv'                         => '{name_legend},name,type,filename;{config_legend},headerFields,export,cliExport;{date_legend:hide},lastRun,skipLastRun',
        'xls'                         => '{name_legend},name,type,filename;{config_legend},useTemplate,headerFields,export,cliExport;{date_legend:hide},lastRun,skipLastRun',
        'xlsx'                        => '{name_legend},name,type,filename;{config_legend},useTemplate,headerFields,export,cliExport;{date_legend:hide},lastRun,skipLastRun',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'export_fields'                 => 'fields',
        'export_tokens'                 => 'tokenFields',
        'cliExport'                     => 'targetPath',
        'useTemplate'                   => 'template,startIndex,sheetIndex',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ),
        'pid' => array
        (
            'sql'                     => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation'                => ['table' => 'tl_lead', 'type' => 'belongsTo'],
        ),
        'tstamp' => array
        (
            'sql'                     => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['name'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => ['type' => 'string', 'default' => ''],
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onTypeOptionsCallback'),
            'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                     => ['type' => 'string', 'length' => 32, 'default' => ''],
        ),
        'filename' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['filename'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('decodeEntities'=>true, 'maxlength'=>128, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'explanation'             => 'leadsTags',
            'sql'                     => ['type' => 'string', 'length' => 128, 'default' => ''],
        ),
        'useTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['useTemplate'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'clr', 'submitOnChange'=>true),
            'sql'                     => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
        ),
        'template' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['template'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
            'sql'                     => ['type' => 'binary_string', 'length' => 16, 'fixed' => true, 'notnull' => false],
        ),
        'startIndex' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['startIndex'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp'=>'digit'),
            'sql'                     => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'sheetIndex' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['sheetIndex'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp'=>'digit'),
            'sql'                     => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ),
        'headerFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['headerFields'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'clr'),
            'sql'                     => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
        ),
        'export' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['export'],
            'default'                 => 'all',
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'radio',
            'options'                 => array('all', 'fields', 'tokens'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['export'],
            'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                     => ['type' => 'string', 'length' => 8, 'default' => ''],
        ),
        'cliExport' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['cliExport'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                     => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
        ),
        'targetPath' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['targetPath'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                     => ['type' => 'string', 'default' => ''],
            'save_callback'           => array(array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onSaveTargetPath')),
        ),
        'fields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('mandatory'=>true, 'dragAndDrop'=>true, 'columnFields'=>array
            (
                'column_display' => array
                (
                    'inputType'               => 'text', // dummy
                    'eval'                    => array('tl_class'=>'column_display', 'hideHead'=>true),
                ),
                'field' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_field'],
                    'exclude'                 => true,
                    'inputType'               => 'select',
                    'options_callback'        => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onExportOptionsCallback'),
                    'eval'                    => array('mandatory'=>true, 'style'=>'width:150px;'),
                ),
                'name' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_name'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('style'=>'width:150px;')
                ),
                'value' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_value'],
                    'exclude'                 => true,
                    'inputType'               => 'select',
                    'options'                 => array('label', 'all', 'value'),
                    'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_value'],
                    'eval'                    => array('style'=>'width:125px;')
                ),
                'format' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_format'],
                    'exclude'                 => true,
                    'inputType'               => 'select',
                    'options_callback'        => array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onFormatOptionsCallback'),
                    'eval'                    => array('style'=>'width:150px;')
                ),
            )),
            'sql'                     => ['type' => 'blob', 'length' => \Doctrine\DBAL\Platforms\MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB, 'notnull' => false],
            'load_callback'           => array(array(Terminal42\LeadsBundle\EventListener\DataContainer\LeadExportListener::class, 'onFieldsLoadCallback'))
        ),
        'tokenFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('mandatory'=>true, 'dragAndDrop'=>true, 'columnFields'=>array
            (
                'targetColumn' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields_targetColumn'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('mandatory'=>true, 'style'=>'width:50px;'),
                ),
                'headerField' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_name'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('style'=>'width:100px;')
                ),
                'tokensValue' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields_tokensValue'],
                    'exclude'                 => true,
                    'inputType'               => 'textarea',
                    'eval'                    => array('style'=>'width:420px;')
                ),
            )),
            'sql'                     => ['type' => 'blob', 'length' => \Doctrine\DBAL\Platforms\MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB, 'notnull' => false],
        ),
        'lastRun' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['lastRun'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'nullIfEmpty'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => ['type' => 'integer', 'notnull' => false],
        ),
        'skipLastRun' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['skipLastRun'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
        ),
    )
);
