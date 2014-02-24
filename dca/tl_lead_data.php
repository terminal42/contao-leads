<?php

/**
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2014, terminal42 gmbh
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
    'fields' => array()
);


class tl_lead_data extends Backend
{

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function listRows($row)
    {
        return $row['name'] . ': ' . Leads::formatValue((object) $row);
    }
}
