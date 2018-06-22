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
            array('tl_lead_data', 'checkPermission')
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
            'child_record_callback' => array('tl_lead_data', 'listRows'),
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


class tl_lead_data extends Backend
{

    /**
     * Check permissions to edit table
     */
    public function checkPermission()
    {
        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            return;
        }

        $objUser->forms = deserialize($objUser->forms);

        if (!is_array($objUser->forms) || empty($objUser->forms)) {
            \System::log('Not enough permissions to access leads data ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objLeads = \Database::getInstance()->prepare("SELECT master_id FROM tl_lead WHERE id=?")
                                            ->limit(1)
                                            ->execute(\Input::get('id'));

        if (!$objLeads->numRows || !in_array($objLeads->master_id, $objUser->forms)) {
            \System::log('Not enough permissions to access leads data ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }
    }

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function listRows($row)
    {
        $label = implode(', ', deserialize($row['label'], true));
        $value = implode(', ', deserialize($row['value'], true));

        if ($label == $value) {
            $value = '';
        }

        return sprintf(
            '
<div style="float:left;width:20%%;margin-right:10px;font-weight:500">%s</div>
<div style="float:left;width:50%%;margin-right:10px">%s</div>
<div style="float:left;width:20%%;color:#b3b3b3;">%s</div>',
            $row['name'],
            $label,
            $value
        );

    }
}
