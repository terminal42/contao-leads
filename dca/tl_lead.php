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
            array('tl_lead', 'checkPermission'),
        ),
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
            'label_callback'    => array('tl_lead', 'getLabel'),
        ),
        'global_operations' => array
        (
            'export' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['export'],
                'class'         => 'header_leads_export',
                'attributes'    => 'onclick="Backend.getScrollOffset();" style="display:none"',
            ),
            'export_csv' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['export_csv'],
                'href'          => 'key=export&amp;type=csv',
                'class'         => 'leads-export header_export_csv',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['show'],
                'href'          => 'key=show',
                'icon'          => 'show.gif'
            ),
            'data' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['data'],
                'href'          => 'table=tl_lead_data',
                'icon'          => 'system/modules/leads/assets/field.png'
            ),
        )
    ),

    // Select
    'select' => array
    (
        'buttons_callback' => array
        (
            array('tl_lead', 'addExportButtons')
        )
    ),

    // Fields
    'fields' => array
    (
        'form_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['form_id'],
            'filter'            => true,
            'sorting'           => true,
            'foreignKey'        => 'tl_form.title',
        ),
        'language' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['language'],
            'filter'            => true,
            'sorting'           => true,
            'options'           => $this->getLanguages(),
        ),
        'created' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['created'],
            'sorting'           => true,
            'flag'              => 8,
            'eval'              => array('rgxp'=>'datim'),
        ),
        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['member'],
            'filter'            => true,
            'sorting'           => true,
            'flag'              => 12,
            'foreignKey'        => "tl_member.CONCAT(lastname, ' ', firstname)"
        ),
    )
);


if (in_array('php-excel', \Config::getInstance()->getActiveModules()) && class_exists('PHPExcel')) {
    $GLOBALS['TL_DCA']['tl_lead']['list']['global_operations']['export_xls'] = array
    (
        'label'         => &$GLOBALS['TL_LANG']['tl_lead']['export_xls'],
        'href'          => 'key=export&amp;type=xls',
        'class'         => 'leads-export header_export_excel',
        'attributes'    => 'onclick="Backend.getScrollOffset();"',
    );
    $GLOBALS['TL_DCA']['tl_lead']['list']['global_operations']['export_xlsx'] = array
    (
        'label'         => &$GLOBALS['TL_LANG']['tl_lead']['export_xlsx'],
        'href'          => 'key=export&amp;type=xlsx',
        'class'         => 'leads-export header_export_excel',
        'attributes'    => 'onclick="Backend.getScrollOffset();"',
    );
}

class tl_lead extends Backend
{

    /**
     * Check if a user has access to lead data
     * @param DataContainer
     */
    public function checkPermission($dc)
    {
        if ($this->Input->get('master') == '')
        {
            $this->redirect('contao/main.php?act=error');
        }
    }


    /**
     * Generate label for this record
     * @param array
     * @param string
     * @return string
     */
    public function getLabel($row, $label)
    {
        $objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE id=?")->execute($row['master_id']);

        // No form found, we can't format the label
        if (!$objForm->numRows)
        {
            return $label;
        }

        $arrTokens = array('created'=>$this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $row['created']));
        $objData = $this->Database->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

        while ($objData->next())
        {
            $varValue = deserialize($objData->value);
            $arrTokens[$objData->name] = is_array($varValue) ? implode(', ', $varValue) : $varValue;
        }

        return $this->parseSimpleTokens($objForm->leadLabel, $arrTokens);
    }


    public function show($dc)
    {
        $objData = $this->Database->prepare("SELECT d.*, l.created, f.title AS form_title, IF(ff.label IS NULL OR ff.label='', d.name, ff.label) AS name FROM tl_lead l LEFT JOIN tl_lead_data d ON l.id=d.pid LEFT OUTER JOIN tl_form f ON l.master_id=f.id LEFT OUTER JOIN tl_form_field ff ON d.master_id=ff.id WHERE l.id=? ORDER BY d.sorting")->execute($dc->id);

        if (!$objData->numRows)
        {
            $this->redirect('contao/main.php?act=error');
        }

        $i = 0;
        $rows = '';

        while ($objData->next())
        {
            $rows .= '
  <tr>
    <td' . ($i%2 ? ' class="tl_bg"' : '') . '><span class="tl_label">' . $objData->name . ': </span></td>
    <td' . ($i%2 ? ' class="tl_bg"' : '') . '>' . Leads::formatValue($objData) . '</td>
  </tr>';

              ++$i;
        }


        return '
<div id="tl_buttons">
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], 'ID ' . $dc->id) . '</h2>

<table class="tl_show">
  <tbody><tr>
    <td><span class="tl_label">ID: </span></td>
    <td>' . $dc->id . '</td>
  </tr>
  <tr>
    <td class="tl_bg"><span class="tl_label">' . $GLOBALS['TL_LANG']['tl_lead']['created'][0] . ': </span></td>
    <td class="tl_bg">' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objData->created) . '</td>
  </tr>
  <tr>
    <td><span class="tl_label">' . $GLOBALS['TL_LANG']['tl_lead']['form_id'][0] . ': </span></td>
    <td>' . $objData->form_title . '</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>' . $rows . '
</tbody></table>
';
    }


    public function export()
    {
        $intMaster = $this->Input->get('master');

        if (!$intMaster) {
            $this->redirect('contao/main.php?act=error');
        }

        $arrIds = is_array($GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root']) ? $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] : null;
        $this->import('Leads');
        $this->Leads->export($intMaster, $this->Input->get('type'), $arrIds);
    }


    public function addExportButtons($arrButtons)
    {
        if (\Input::post('FORM_SUBMIT') == 'tl_select') {
            $arrIds = \Input::post('IDS');

            if (empty($arrIds)) {
                $this->reload();
            }

            $this->import('Leads');

            if (\Input::post('export_csv')) {
                $this->Leads->export($this->Input->get('master'), 'csv', $arrIds);
            } elseif (\Input::post('export_xls')) {
                $this->Leads->export($this->Input->get('master'), 'xls', $arrIds);
            } elseif (\Input::post('export_xlsx')) {
                $this->Leads->export($this->Input->get('master'), 'xlsx', $arrIds);
            }
        }

        $arrButtons['export_csv'] = '<input type="submit" name="export_csv" id="export_csv" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0] . ' ' . $GLOBALS['TL_LANG']['tl_lead']['export_csv'][0]).'">';

        if (class_exists('PHPExcel')) {
            $arrButtons['export_xls'] = '<input type="submit" name="export_csv" id="export_xls" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0] . ' ' . $GLOBALS['TL_LANG']['tl_lead']['export_xls'][0]).'">';
            $arrButtons['export_xlsx'] = '<input type="submit" name="export_csv" id="export_xlsx" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0] . ' ' . $GLOBALS['TL_LANG']['tl_lead']['export_xlsx'][0]).'">';
        }

        return $arrButtons;
    }
}
