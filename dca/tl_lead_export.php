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
            array('tl_lead_export', 'updatePalette')
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
        '__selector__'                => array('type'),
        'default'                     => '{name_legend},name,type,filename;{config_legend},export',
        'csv'                         => '{name_legend},name,type,filename;{config_legend},headerFields,export',
        'xls'                         => '{name_legend},name,type,filename;{config_legend},headerFields,export',
        'xlsx'                        => '{name_legend},name,type,filename;{config_legend},headerFields,export',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'export'                      => 'fields'
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
            'options'                 => array_keys($GLOBALS['LEADS_EXPORT']),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['type'],
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
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
        'headerFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['headerFields'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'export' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['export'],
            'default'                 => 'all',
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'radio',
            'options'                 => array('all', 'fields'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_export']['export'],
            'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                     => "varchar(8) NOT NULL default ''"
        ),
        'fields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_export']['fields'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('mandatory'=>true, 'columnFields'=>array
            (
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
                    'options'                 => array('raw', 'date', 'datim', 'time'),
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
     * Check permissions to edit table
     */
    public function checkPermission()
    {
        if (!\BackendUser::getInstance()->isAdmin) {
            \System::log('Not enough permissions to access leads export ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Update the palette depending on the export type
     *
     * @param object $dc
     */
    public function updatePalette($dc = null)
    {
        if (!$dc->id) {
            return;
        }

        $objRecord = \Database::getInstance()->prepare(
            "SELECT * FROM tl_lead_export WHERE id=?"
        )->execute($dc->id);

        if (!$objRecord->export || $objRecord->export == 'all') {
            return;
        }

        $strPalette = $objRecord->type ? $objRecord->type : 'default';
        $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette] = str_replace('export', 'export,' . $GLOBALS['TL_DCA']['tl_lead_export']['subpalettes']['export'], $GLOBALS['TL_DCA']['tl_lead_export']['palettes'][$strPalette]);
    }

    /**
     * Generate the label and return it as HTML string
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
     * Load the lead fields
     *
     * @param mixed  $varValue
     * @param object $dc
     *
     * @return string
     */
    public function loadLeadFields($varValue, $dc = null)
    {
        $arrFields = deserialize($varValue, true);

        // Load the form fields
        if (empty($arrFields) && $dc->id) {

            // Form ID
            $arrFields[] = array(
                'field' => '_form',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
                'value' => 'all',
                'format' => 'raw'
            );

            // Date created
            $arrFields[] = array(
                'field' => '_created',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
                'value' => 'all',
                'format' => 'datim'
            );

            // Member ID
            $arrFields[] = array(
                'field' => '_member',
                'name' => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
                'value' => 'all',
                'format' => 'raw'
            );

            $objFields = Database::getInstance()->prepare(
                "SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)"
            )->execute($dc->id);

            while ($objFields->next()) {
                $arrFields[] = array(
                    'field' => $objFields->id,
                    'name' => '',
                    'value' => 'all',
                    'format' => 'raw',
                );
            }
        }

        return serialize($arrFields);
    }

    /**
     * Get the export fields as array
     *
     * @return array
     */
    public function getExportFields()
    {
        if (!\Input::get('id')) {
            return array();
        }

        $arrFields = array(
            '_form' => $GLOBALS['TL_LANG']['tl_lead_export']['field_form'],
            '_created' => $GLOBALS['TL_LANG']['tl_lead_export']['field_created'],
            '_member' => $GLOBALS['TL_LANG']['tl_lead_export']['field_member'],
        );

        $objFields = \Database::getInstance()->prepare("SELECT * FROM tl_form_field WHERE leadStore!='' AND pid=(SELECT pid FROM tl_lead_export WHERE id=?)")
                                             ->execute(Input::get('id'));

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
}
