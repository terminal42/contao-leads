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
 * Config
 */
$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = 'tl_lead_export';
$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = array('tl_form_lead', 'modifyPalette');

/**
 * Operations
 */
$GLOBALS['TL_DCA']['tl_form']['list']['operations']['lead_export'] = array
(
    'label'               => &$GLOBALS['TL_LANG']['tl_form']['lead_export'],
    'href'                => 'table=tl_lead_export',
    'icon'                => 'system/modules/leads/assets/export.png',
    'button_callback'     => array('tl_form_lead', 'exportIcon')
);

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
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMaster'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMaster'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array('tl_form_lead', 'getMasterForms'),
    'eval'                  => array('submitOnChange'=>true, 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form']['leadMasterBlankOptionLabel'], 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadMenuLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadMenuLabel'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_form']['fields']['leadLabel'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form']['leadLabel'],
    'exclude'               => true,
    'inputType'             => 'textarea',
    'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'style'=>'height:60px', 'allowHtml'=>true, 'tl_class'=>'clr'),
);


class tl_form_lead extends Backend
{

    /**
     * Modify the palette based on configuration. We can't use simple subpalettes because we do complex things...
     * @param DataContainer
     */
    public function modifyPalette($dc)
    {
        $strPalette = 'leadEnabled';
        $objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=" . (int) $dc->id);

        if ($objForm->leadEnabled)
        {
            $strPalette .= ',leadMaster';

            if ($objForm->leadMaster == 0)
            {
                $strPalette .= ',leadMenuLabel,leadLabel';
            }
        }

        $GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace('storeValues', 'storeValues,'.$strPalette, $GLOBALS['TL_DCA']['tl_form']['palettes']['default']);
    }


    public function getMasterForms($dc)
    {
        $arrForms = array();
        $objForms = $this->Database->execute("SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 AND id!=" . (int) $dc->id);

        while ($objForms->next())
        {
            $arrForms[$objForms->id] = $objForms->title;
        }

        return $arrForms;
    }

    /**
     * Return the export icon
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function exportIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (!BackendUser::getInstance()->isAdmin || !$row['leadEnabled']) {
            return '';
        }

        return '<a href="'.$this->addToUrl($href . '&amp;id=' . $row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }
}
