<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\DataContainer;

class FormFieldListener
{
    public function onLoadCallback(DataContainer $dc): void
    {
        global $objLeadForm;

        switch ($_GET['act']) {
            case 'edit':
                $objLeadForm = \Database::getInstance()->prepare(
                    'SELECT leadEnabled, leadMaster FROM tl_form WHERE id=(SELECT pid FROM tl_form_field WHERE id=?)'
                )->execute($dc->id);
                break;

            case 'editAll':
            case 'overrideAll':
                $objLeadForm = \Database::getInstance()->prepare('SELECT leadEnabled, leadMaster FROM tl_form WHERE id=?')->execute($dc->id);
                break;

            default:
                return;
        }

        if (!$objLeadForm->leadEnabled) {
            unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']);
        } else {
            if (!$objLeadForm->leadMaster) {
                $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options'] = ['1' => $GLOBALS['TL_LANG']['MSC']['yes']];
                $GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['eval']['blankOptionLabel'] = $GLOBALS['TL_LANG']['MSC']['no'];

                unset($GLOBALS['TL_DCA']['tl_form_field']['fields']['leadStore']['options_callback']);
            }

            foreach ($GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $strName => $strPalette) {
                if (\in_array($strName, ['__selector__', 'submit', 'default', 'headline', 'explanation'], true)) {
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

        $arrFields = [];
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
            $arrFields[$objFields->id] = empty($objFields->label) ? $objFields->name : ($objFields->label.' ('.$objFields->name.')');
        }

        return $arrFields;
    }
}
