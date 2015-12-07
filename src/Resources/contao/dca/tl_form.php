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
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = array('tl_form_lead', 'modifyPalette');

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadEnabled';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'leadMaster';

/**
 * Fields
 */
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
    'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
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
        $arrForms = array();
        $objForms = \Database::getInstance()->execute("SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 AND id!=" . (int) $dc->id);

        while ($objForms->next()) {
            $arrForms[$objForms->id] = $objForms->title;
        }

        return $arrForms;
    }
}
