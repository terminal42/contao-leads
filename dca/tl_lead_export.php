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
            array('tl_lead_export', 'checkPermission'),
            array('tl_lead_export', 'updatePalette'),
            array('tl_lead_export', 'loadJsAndCss'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
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
            'child_record_callback'   => array('tl_lead_export', 'generateLabel'),
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
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
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
        '__selector__'                => array('type', 'useTemplate', 'export'),
        'default'                     => '{name_legend},name,type,filename;{config_legend},export;{date_legend:hide},lastRun,skipLastRun',
        'csv'                         => '{name_legend},name,type,filename;{config_legend},headerFields,export;{date_legend:hide},lastRun,skipLastRun',
        'xls'                         => '{name_legend},name,type,filename;{config_legend},useTemplate,headerFields,export;{date_legend:hide},lastRun,skipLastRun',
        'xlsx'                        => '{name_legend},name,type,filename;{config_legend},useTemplate,headerFields,export;{date_legend:hide},lastRun,skipLastRun',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'export_all'                    => 'exportValue',
        'export_fields'                 => 'fields',
        'export_tokens'                 => 'tokenFields',
        'useTemplate'                   => 'template,startIndex,sheetIndex',
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
        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['name'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['type'],
            'default'                 => key($GLOBALS['LEADS_EXPORT']),
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => function() {

                $options = array();

                foreach (array_keys($GLOBALS['LEADS_EXPORT']) as $exporterClassKey) {

                    $exporterDefinition = $GLOBALS['LEADS_EXPORT'][$exporterClassKey];

                    if (!is_array($exporterDefinition)) {

                        /** @var Leads\Exporter\ExporterInterface $exporter */
                        $exporter = new $exporterDefinition();

                        if ($exporter instanceof \Leads\Exporter\ExporterInterface) {
                            if ($exporter->isAvailable()) {
                                $options[] = $exporterClassKey;
                            }
                        }
                    } else {
                        // Backwards compatibility
                        $options[] = $exporterClassKey;
                    }
                }

                return $options;
            },
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['type'],
            'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'filename' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['filename'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('decodeEntities'=>true, 'maxlength'=>128, 'helpwizard'=>true, 'tl_class'=>'w50'),
            'explanation'             => 'leadsTags',
            'sql'                     => "varchar(128) NOT NULL default ''"
        ),
        'useTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['useTemplate'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'clr', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'template' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['template'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
            'sql'                     => "binary(16) NULL",
        ),
        'startIndex' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['startIndex'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp'=>'digit'),
            'sql'                     => "int(10) NOT NULL default '0'"
        ),
        'sheetIndex' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['sheetIndex'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp'=>'digit'),
            'sql'                     => "int(10) NOT NULL default '0'"
        ),
        'headerFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['headerFields'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'clr'),
            'sql'                     => "char(1) NOT NULL default ''"
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
            'sql'                     => "varchar(8) NOT NULL default ''"
        ),
        'exportValue' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_value'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'all',
            'options'                 => array('label', 'all', 'value'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_value'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(8) NOT NULL default 'all'"
        ),
        'fields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('mandatory'=>true, 'dragAndDrop' => true, 'columnFields'=>array
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
                    'options_callback'        => array('tl_lead_export', 'getExportFields'),
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
                    'options_callback'        => function() {

                        $options = array();

                        foreach (array_keys($GLOBALS['LEADS_DATA_TRANSFORMERS']) as $transformerClassKey) {

                            /** @var Leads\DataTransformer\DataTransformerInterface $transformer */
                            $transformer = new $GLOBALS['LEADS_DATA_TRANSFORMERS'][$transformerClassKey]();

                            if ($transformer instanceof \Leads\DataTransformer\DataTransformerInterface
                                && $transformer instanceof \Leads\DataTransformer\DisplayInBackendInterface
                            ) {
                                    $options[] = $transformerClassKey;
                            }
                        }

                        // Backwards compatibility
                        return array_merge(
                            (array) $GLOBALS['TL_DCA']['tl_lead_export']['fields']['fields']['eval']['columnFields']['format']['options'],
                            $options
                        );
                    },
                    'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['fields_format'],
                    'eval'                    => array('style'=>'width:150px;')
                ),
            )),
            'sql'                     => "blob NULL",
            'load_callback' => array
            (
                array('tl_lead_export', 'loadLeadFields')
            )
        ),
        'tokenFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['tokenFields'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('mandatory'=>true, 'columnFields'=>array
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
            'sql'                     => "blob NULL",
        ),
        'lastRun' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['lastRun'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'nullIfEmpty'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => 'int(10) NULL'
        ),
        'skipLastRun' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['skipLastRun'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
    )
);


/**
 * Class tl_lead_export
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_lead_export extends Backend
{

    /**
     * Check permissions to edit table.
     */
    public function checkPermission()
    {
        $user = \BackendUser::getInstance();

        if (!$user->isAdmin && !$user->canEditFieldsOf('tl_lead_export')) {
            \System::log('Not enough permissions to access leads export ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Update the palette depending on the export type.
     *
     * @param \DataContainer $dc
     */
    public function updatePalette($dc = null)
    {
        if (!$dc->id) {
            return;
        }

        $objRecord = \Database::getInstance()->prepare(
            "SELECT * FROM tl_lead_export WHERE id=?"
        )->execute($dc->id);

        if (!$objRecord->export || 'all' === $objRecord->export) {
            return;
        }

        $strPalette = $objRecord->type ? $objRecord->type : 'default';
        $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette] = str_replace(
            'export',
            'export,' . $GLOBALS['TL_DCA']['tl_lead_export']['subpalettes']['export'],
            $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette]
        );
    }

    /**
     * Generate the label and return it as HTML string.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function generateLabel($arrRow)
    {
        return '<div>' . $arrRow['name'] . '</div>';
    }

    /**
     * Load the lead fields.
     *
     * @param mixed  $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function loadLeadFields($varValue, $dc = null)
    {
        $arrFields = deserialize($varValue, true);

        // Load the form fields
        if (empty($arrFields) && $dc->id) {
            $arrFields = array_values(\Leads\Leads::getSystemColumns());

            $objFields = Database::getInstance()->prepare(
                "SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)"
            )->execute($dc->id);

            while ($objFields->next()) {
                $arrFields[] = array(
                    'field'  => $objFields->id,
                    'name'   => '',
                    'value'  => 'all',
                    'format' => 'raw',
                );
            }
        }

        return serialize($arrFields);
    }

    /**
     * Get the export fields as array.
     *
     * @return array
     */
    public function getExportFields()
    {
        if (!\Input::get('id')) {
            return array();
        }

        $arrFields = array();

        $systemColumns = \Leads\Leads::getSystemColumns();

        foreach ($systemColumns as $k => $systemColumn) {
            $arrFields[$k] = $systemColumn['name'];
        }

        $objFields = \Database::getInstance()
            ->prepare("SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)")
            ->execute(Input::get('id'))
        ;

        while ($objFields->next()) {
            $strLabel = $objFields->name;

            // Use the field label
            if ($objFields->label != '') {
                $strLabel = $objFields->label . ' [' . $objFields->name . ']';
            }

            $arrFields[$objFields->id] = $strLabel;
        }

        return $arrFields;
    }

    /**
     * Loads JS and CSS.
     */
    public function loadJsAndCss()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = $GLOBALS['BE_MOD']['leads']['lead']['javascript'];
        $GLOBALS['TL_CSS'][] = $GLOBALS['BE_MOD']['leads']['lead']['stylesheet'];
    }
}
