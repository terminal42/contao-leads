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
 * Config
 */
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_lead_export';
$GLOBALS['TL_DCA']['tl_form']['config']['oncopy_callback'][] = array('tl_form_lead', 'onCopyCallback');
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = array('tl_form_lead', 'modifyPalette');
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadEnabled'] = 'index';
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadMaster'] = 'index';
$GLOBALS['TL_DCA']['tl_form']['config']['sql']['keys']['leadEnabled,leadMaster'] = 'index';

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadEnabled';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadMaster';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['title']['eval']['decodeEntities'] = true;

$GLOBALS['TL_DCA']['tl_form']['fields']['leadEnabled'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadEnabled'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => array('tl_class'=>'clr', 'submitOnChange'=>true),
    'sql'                   => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMaster'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMaster'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array('tl_form_lead', 'getMasterForms'),
    'eval'                  => array(
        'submitOnChange'=>true,
        'includeBlankOption'=>true,
        'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'],
        'tl_class'=>'w50'
    ),
    'sql'                   => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMenuLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50', 'decodeEntities'=>true),
    'sql'                   => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadLabel'],
    'exclude'               => true,
    'inputType'             => 'textarea',
    'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'style'=>'height:60px', 'allowHtml'=>true, 'tl_class'=>'clr'),
    'sql'                   => "text NULL"
);


class tl_form_lead extends Backend
{
    /**
     * On copy callback
     *
     * @param int            $id
     * @param \DataContainer $dc
     */
    public function onCopyCallback($id, \DataContainer $dc)
    {
        $db = \Database::getInstance();
        $exports = $db->prepare("SELECT id, fields FROM tl_lead_export WHERE pid=?")->execute($id);

        if (!$exports->numRows) {
            return;
        }

        $oldFormFields = $db->prepare("SELECT id FROM tl_form_field WHERE pid=? ORDER BY sorting")->execute($dc->id);
        $newFormFields = $db->prepare("SELECT id FROM tl_form_field WHERE pid=? ORDER BY sorting")->execute($id);

        // Create the fields mapper
        $fieldsMapper = array_combine($oldFormFields->fetchEach('id'), $newFormFields->fetchEach('id'));

        while ($exports->next()) {
            $fields = deserialize($exports->fields, true);

            // Map the fields
            foreach ($fields as $k => $v) {
                if (isset($fieldsMapper[$v['field']])) {
                    $fields[$k]['field'] = $fieldsMapper[$v['field']];
                }
            }

            $db->prepare('UPDATE tl_lead_export SET fields=? WHERE id=?')->execute(serialize($fields), $exports->id);
        }
    }

    /**
     * Modify the palette based on configuration. We can't use simple subpalettes
     * because we do more complex things.
     *
     * @param   $dc
     */
    public function modifyPalette($dc)
    {
        $strPalette = 'leadEnabled';
        $objForm = \Database::getInstance()->execute("SELECT * FROM tl_form WHERE id=" . (int) $dc->id);

        if ($objForm->leadEnabled) {
            $strPalette .= ',leadMaster';

            if ($objForm->leadMaster == 0) {
                $strPalette .= ',leadMenuLabel,leadLabel';
            }
        }

        $GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace('storeValues', 'storeValues,'.$strPalette, $GLOBALS['TL_DCA']['tl_form']['palettes']['default']);
    }

    /**
     * Gets the master forms.
     *
     * @param $dc
     *
     * @return array
     */
    public function getMasterForms($dc)
    {
        $user = \Contao\BackendUser::getInstance();
        $filter = null;
        
        // Check user permissions
        if (!$user->isAdmin) {
            if (!is_array($user->forms) || empty($user->forms)) {
                return [];
            }

            $filter = $user->forms;
        }

        $arrForms = array();
        $objForms = \Database::getInstance()->execute("SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 AND id!=" . (int) $dc->id . (($filter !== null) ? " AND id IN(" . implode(',', $filter) . ")" : ""));

        while ($objForms->next()) {
            $arrForms[$objForms->id] = $objForms->title;
        }

        return $arrForms;
    }
}
