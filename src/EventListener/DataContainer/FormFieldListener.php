<?php

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\DataContainer;

class FormFieldListener
{
    public function onLoadCallback(DataContainer $dc)
    {
        global $objLeadForm;

        switch ($_GET['act']) {
            case 'edit':
                $objLeadForm = \Database::getInstance()->prepare("SELECT leadEnabled, leadMaster FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id=?)")
                                        ->execute($dc->id);
                break;

            case 'editAll':
            case 'overrideAll':
                $objLeadForm = \Database::getInstance()->prepare("SELECT leadEnabled, leadMaster FROM tl_form WHERE id=?")->execute($dc->id);
                break;

            default:
                return;
        }

        if (!$objLeadForm->leadEnabled) {
            unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']);
        } else {
            if ($objLeadForm->leadMaster == 0) {
                $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options'] = array('1'=> $GLOBALS['TL_LANG']['MSC']['yes']);
                $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['eval']['blankOptionLabel'] = $GLOBALS['TL_LANG']['MSC']['no'];

                unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options_callback']);
            }

            foreach ($GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $strName => $strPalette) {

                if (in_array($strName, array('__selector__', 'submit', 'default', 'headline', 'explanation'))) {
                    continue;
                }

                $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$strName] = str_replace(',type,', ',type,leadStore,', $strPalette);
            }

            $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['eval']['tl_class'] = 'w50';
        }
    }

    /**
     * Returns the options for the "leadStore" field.
     *
     * @param $dc
     *
     * @return array
     */
    public function onLeadStoreOptions($dc)
    {
        global $objLeadForm;

        $arrFields = array();
        $objFields = \Database::getInstance()->prepare("
            SELECT *
            FROM tl_form_field
            WHERE
                name!=''
                AND pid=?
                AND leadStore='1'
                AND id NOT IN (
                    SELECT leadStore
                    FROM tl_form_field
                    WHERE pid=? AND id!=?
                )
            ORDER BY sorting
        ")->execute($objLeadForm->leadMaster, $objLeadForm->id, $dc->activeRecord->id);

        while ($objFields->next()) {
            $arrFields[$objFields->id] = $objFields->label == '' ? $objFields->name : ($objFields->label . ' (' . $objFields->name . ')');
        }

        return $arrFields;
    }
}
