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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\StringUtil;

class FormListener
{
    public function onLoadCallback(DataContainer $dc): void
    {
        $objForm = \Database::getInstance()->execute('SELECT * FROM tl_form WHERE id='.(int) $dc->id);

        if (!$objForm->leadMaster) {
            $pm = PaletteManipulator::create();

            $pm
                ->addField('leadMenuLabel', null, PaletteManipulator::POSITION_APPEND)
                ->addField('leadLabel', null, PaletteManipulator::POSITION_APPEND)
                ->addField('leadPeriod', null, PaletteManipulator::POSITION_APPEND)
                ->addField('leadPurgeUploads', null, PaletteManipulator::POSITION_APPEND)
                ->applyToSubpalette('leadEnabled', 'tl_form')
            ;
        }
    }

    /**
     * On copy callback.
     *
     * @param int $id
     */
    public function onCopyCallback($id, \DataContainer $dc): void
    {
        $db = \Database::getInstance();
        $exports = $db->prepare('SELECT id, fields FROM tl_lead_export WHERE pid=?')->execute($id);

        if (!$exports->numRows) {
            return;
        }

        $oldFormFields = $db->prepare('SELECT id FROM tl_form_field WHERE pid=? ORDER BY sorting')->execute($dc->id);
        $newFormFields = $db->prepare('SELECT id FROM tl_form_field WHERE pid=? ORDER BY sorting')->execute($id);

        // Create the fields mapper
        $fieldsMapper = array_combine($oldFormFields->fetchEach('id'), $newFormFields->fetchEach('id'));

        while ($exports->next()) {
            $fields = StringUtil::deserialize($exports->fields, true);

            // Map the fields
            foreach ($fields as $k => $v) {
                if (isset($fieldsMapper[$v['field']])) {
                    $fields[$k]['field'] = $fieldsMapper[$v['field']];
                }
            }

            $db->prepare('UPDATE tl_lead_export SET fields=? WHERE id=?')->execute(serialize($fields), $exports->id);
        }
    }

    public function onLeadMasterOptions($dc)
    {
        $user = \Contao\BackendUser::getInstance();
        $filter = null;

        // Check user permissions
        if (!$user->isAdmin) {
            if (!\is_array($user->forms) || empty($user->forms)) {
                return [];
            }

            $filter = $user->forms;
        }

        $arrForms = [];
        $objForms = \Database::getInstance()->execute("SELECT id, title FROM tl_form WHERE leadEnabled='1' AND leadMaster=0 AND id!=".(int) $dc->id.((null !== $filter) ? ' AND id IN('.implode(',', $filter).')' : ''));

        while ($objForms->next()) {
            $arrForms[$objForms->id] = $objForms->title;
        }

        return $arrForms;
    }
}
