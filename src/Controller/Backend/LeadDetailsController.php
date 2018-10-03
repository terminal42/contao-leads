<?php

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\System;

class LeadDetailsController
{
    /**
     * Override the default "show" dialog.
     *
     * @param $dc
     *
     * @return string
     */
    public function __invoke($dc)
    {
        $arrLanguages = System::getLanguages();

        $objForm = Database::getInstance()->prepare("
            SELECT l.*, s.title AS form_title, f.title AS master_title, CONCAT(m.firstname, ' ', m.lastname) AS member_name
            FROM tl_lead l
            LEFT OUTER JOIN tl_form s ON l.form_id=s.id
            LEFT OUTER JOIN tl_form f ON l.master_id=f.id
            LEFT OUTER JOIN tl_member m ON l.member_id=m.id
            WHERE l.id=?
        ")->execute($dc->id);

        $objData = Database::getInstance()->prepare("
            SELECT d.*, IF(ff.label IS NULL OR ff.label='', d.name, ff.label) AS name
            FROM tl_lead_data d
            LEFT OUTER JOIN tl_form_field ff ON d.master_id=ff.id
            WHERE d.pid=?
            ORDER BY d.sorting
        ")->execute($dc->id);

        /** @var \BackendTemplate|object $template */
        $template = new BackendTemplate('be_leads_show');
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
                'value' => \Terminal42\LeadsBundle\Leads::formatValue($objData),
                'class' => ($i % 2) ? 'tl_bg' : '',
            );

            ++$i;
        }

        $template->data = $rows;

        return $template->parse();
    }
}
