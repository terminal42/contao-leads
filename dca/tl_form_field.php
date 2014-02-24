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
$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = array('tl_form_field_leads', 'injectFieldSelect');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreSelect'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['leadStoreSelect'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array('tl_form_field_leads', 'getMasterFields'),
    'eval'                  => array('tl_class'=>'w50', 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_form_field']['leadStoreSelect'][2]),
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreCheckbox'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['leadStoreCheckbox'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => array('tl_class'=>'w50 m12'),
);


class tl_form_field_leads extends Backend
{

    public function injectFieldSelect($dc)
    {
        $intId = 0;
        switch ($this->Input->get('act'))
        {
            case 'edit':
                $intId = $this->Database->execute("SELECT pid FROM tl_form_field WHERE id=$dc->id")->pid;
                break;
            case 'editAll':
            case 'overrideAll':
                $intId = $this->Input->get('id');
                break;
        }

        if ($intId === 0)
        {
            return;
        }

        $objForm = $this->Database->execute("SELECT leadEnabled,leadMaster FROM tl_form WHERE id=$intId");

        if ($objForm->leadEnabled)
        {
            $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore'] = ($objForm->leadMaster ? $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreSelect'] : $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStoreCheckbox']);

            foreach( $GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $strName => $strPalette )
            {
                if (in_array($strName, array('__selector__', 'submit', 'default', 'headline', 'explanation')))
                {
                    continue;
                }

                $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$strName] = str_replace(',type,', ',type,leadStore,', $strPalette);
            }

            $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
        }
    }


    public function getMasterFields($dc)
    {
        $arrFields = array();
        $objForm = $this->Database->execute("SELECT * FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id={$dc->id})");

        if ($objForm->leadEnabled && $objForm->leadMaster > 0)
        {
            $objFields = $this->Database->prepare("SELECT * FROM tl_form_field WHERE name!='' AND pid=? AND leadStore='1' AND id NOT IN (SELECT leadStore FROM tl_form_field WHERE pid=? AND id!=?) ORDER BY sorting")->execute($objForm->leadMaster, $objForm->id, $dc->activeRecord->id);

            while ($objFields->next())
            {
                $arrFields[$objFields->id] = $objFields->label == '' ? $objFields->name : ($objFields->label . ' (' . $objFields->name . ')');
            }
        }

        return $arrFields;
    }
}
