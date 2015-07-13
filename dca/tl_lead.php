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
            array('tl_lead', 'loadExportConfigs'),
            array('tl_lead', 'checkPermission'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary'
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
            'label_callback'    => array('tl_lead', 'getLabel'),
        ),
        'global_operations' => array
        (
            'export_config' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_lead']['export_config'],
                'icon'            => 'settings.gif',
                'class'           => 'leads-export',
                'attributes'      => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => array('tl_lead', 'exportConfigIcon')
            ),
            'export' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_lead']['export'],
                'class'         => 'header_leads_export',
                'attributes'    => 'onclick="Backend.getScrollOffset();" style="display:none"',
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
        'id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                  => "int(10) unsigned NOT NULL default '0'"
        ),
        'master_id' => array
        (
            'sql'                  => "int(10) unsigned NOT NULL default '0'"
        ),
        'form_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['form_id'],
            'filter'            => true,
            'sorting'           => true,
            'foreignKey'        => 'tl_form.title',
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),
        'language' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['language'],
            'filter'            => true,
            'sorting'           => true,
            'options'           => \System::getLanguages(),
            'sql'               => "varchar(2) NOT NULL default ''"
        ),
        'created' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['created'],
            'sorting'           => true,
            'flag'              => 8,
            'eval'              => array('rgxp'=>'datim'),
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),
        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_lead']['member'],
            'filter'            => true,
            'sorting'           => true,
            'flag'              => 12,
            'foreignKey'        => "tl_member.CONCAT(lastname, ' ', firstname)",
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),
        'post_data' => array
        (
            'sql'               => "mediumblob NULL"
        ),
    )
);

class tl_lead extends Backend
{

    /**
     * Load the export configs.
     */
    public function loadExportConfigs()
    {
        $objConfigs = \Database::getInstance()->prepare("SELECT * FROM tl_lead_export WHERE pid=? AND tstamp>0 ORDER BY name")
                                              ->execute(\Input::get('master'));

        if (!$objConfigs->numRows) {
            return;
        }

        $arrOperations = array();

        while ($objConfigs->next()) {
            $arrOperations['export_' . $objConfigs->id] = array
            (
                'label'         => $objConfigs->name,
                'href'          => 'key=export&amp;config=' . $objConfigs->id,
                'class'         => 'leads-export header_export_excel',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            );
        }

        array_insert($GLOBALS['TL_DCA']['tl_lead']['list']['global_operations'], 0, $arrOperations);
    }


    /**
     * Check if a user has access to lead data.
     *
     * @param $dc
     */
    public function checkPermission($dc)
    {
        if (\Input::get('master') == '') {
            \Controller::redirect('contao/main.php?act=error');
        }

        $objUser = \BackendUser::getInstance();

        if ($objUser->isAdmin) {
            return;
        }

        if (!is_array($objUser->forms) || !in_array(\Input::get('master'), $objUser->forms)) {
            \System::log('Not enough permissions to access leads ID "'.\Input::get('master').'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }
    }


    /**
     * Generate label for this record.
     *
     * @param array
     * @param string
     *
     * @return string
     */
    public function getLabel($row, $label)
    {
        $objForm = \Database::getInstance()->prepare("SELECT * FROM tl_form WHERE id=?")->execute($row['master_id']);

        // No form found, we can't format the label
        if (!$objForm->numRows) {

            return $label;
        }

        $arrTokens = array(
            'created' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['created'])
        );

        $objData = \Database::getInstance()->prepare("SELECT * FROM tl_lead_data WHERE pid=?")->execute($row['id']);

        while ($objData->next()) {
            $varValue = deserialize($objData->value);
            $arrTokens[$objData->name] = is_array($varValue) ? implode(', ', $varValue) : $varValue;
        }

        return \String::parseSimpleTokens($objForm->leadLabel, $arrTokens);
    }

    /**
     * Return the export config icon.
     *
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function exportConfigIcon($href, $label, $title, $class, $attributes)
    {
        if (!\BackendUser::getInstance()->isAdmin) {

            return '';
        }

        return '<a href="contao/main.php?do=form&amp;table=tl_lead_export&amp;id=' . Input::get('master') . '" class="'.$class.'" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }

    /**
     * Override the default "show" dialog.
     *
     * @param $dc
     *
     * @return string
     */
    public function show($dc)
    {
        $arrLanguages = \System::getLanguages();

        $objForm = \Database::getInstance()->prepare("
            SELECT l.*, s.title AS form_title, f.title AS master_title, CONCAT(m.firstname, ' ', m.lastname) AS member_name
            FROM tl_lead l
            LEFT OUTER JOIN tl_form s ON l.form_id=s.id
            LEFT OUTER JOIN tl_form f ON l.master_id=f.id
            LEFT OUTER JOIN tl_member m ON l.member_id=m.id
            WHERE l.id=?
        ")->execute($dc->id);

        $objData = \Database::getInstance()->prepare("
            SELECT d.*, IF(ff.label IS NULL OR ff.label='', d.name, ff.label) AS name
            FROM tl_lead_data d
            LEFT OUTER JOIN tl_form_field ff ON d.master_id=ff.id
            WHERE d.pid=?
            ORDER BY d.sorting
        ")->execute($dc->id);

        $template = new \BackendTemplate('be_leads_show');
        $template->recordId         = $dc->id;
        $template->referer          = \System::getReferer(true);
        $template->subheadline      = sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], 'ID ' . $dc->id);
        $template->createdLabel     = $GLOBALS['TL_LANG']['tl_lead']['created'][0];
        $template->createdValue     = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $objForm->created);
        $template->formLabel        = $GLOBALS['TL_LANG']['tl_lead']['form_id'][0];
        $template->formTitle        = $objForm->form_title;
        $template->formId           = $objForm->form_id;

        $template->isMasterForm     = $objForm->master_id == $objForm->form_id;
        $template->masterLabel      = $GLOBALS['TL_LANG']['tl_lead']['master_id'][0];
        $template->masterTitle      = $objForm->master_title;
        $template->masterId         = $objForm->master_id;

        $template->languageLabel    = $GLOBALS['TL_LANG']['tl_lead']['language'][0];
        $template->languageTrans    = $arrLanguages[$objForm->language];
        $template->languageValue    = $objForm->language;

        $template->hasMember        = $objForm->member_id > 0;
        $template->memberLabel      = $GLOBALS['TL_LANG']['tl_lead']['member'][0];
        $template->memberName       = $objForm->member_name;
        $template->memberId         = $objForm->member_id;

        $i = 0;
        $rows = array();

        while ($objData->next()) {
            $rows[] = array(
                'label' => $objData->name,
                'value' => \Leads\Leads::formatValue($objData),
                'class' => ($i % 2 ? 'tl_bg' : '')
            );

            ++$i;
        }

        $template->data = $rows;

        return $template->parse();
    }

    /**
     * Exports according to a config.
     */
    public function export()
    {
        $intConfig = \Input::get('config');

        if (!$intConfig) {
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrIds = is_array($GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root']) ? $GLOBALS['TL_DCA']['tl_lead']['list']['sorting']['root'] : null;

        \Leads\Leads::export($intConfig, $arrIds);
    }

    /**
     * Adds the export buttons to the buttons bar and exports the data.
     *
     * @param array $arrButtons
     *
     * @return mixed
     */
    public function addExportButtons($arrButtons)
    {
        $arrConfigs = \Database::getInstance()->prepare("SELECT id, name FROM tl_lead_export WHERE pid=? ORDER BY name")
                                              ->execute(\Input::get('master'))
                                              ->fetchAllAssoc();

        // Run the export
        if (\Input::post('FORM_SUBMIT') == 'tl_select') {
            $arrIds = \Input::post('IDS');

            if (empty($arrIds)) {
                \Controller::reload();
            }

            foreach ($arrConfigs as $config) {
                if (\Input::post('export_' . $config['id'])) {
                    \Leads\Leads::export($config['id'], $arrIds);
                }
            }
        }

        \System::loadLanguageFile('tl_lead_export');

        // Generate buttons
        foreach ($arrConfigs as $config) {
            $arrButtons['export_' . $config['id']] = '<input type="submit" name="export_' . $config['id'] . '" id="export_' . $config['id'] . '" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['tl_lead']['export'][0] . ' "' . $config['name'] . '"').'">';
        }

        return $arrButtons;
    }
}
