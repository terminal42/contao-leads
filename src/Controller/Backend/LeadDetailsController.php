<?php

/** @noinspection StaticInvocationViaThisInspection */
/** @noinspection PhpTranslationDomainInspection */
/** @noinspection PhpTranslationKeyInspection */

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class LeadDetailsController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var System
     */
    private $systemAdapter;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(ContaoFramework $framework , Environment $twig)
    {
        $this->framework = $framework;
        $this->twig = $twig;

        $this->systemAdapter = $framework->getAdapter(System::class);
    }

    /**
     * @Route("/contao/lead/{id}/details", name="terminal42_leads.details", defaults={"_scope"="backend"})
     */
    public function __invoke(int $id)
    {
        $this->framework->initialize();

        $formData = $this->getFormData($id);
        $languages = $this->systemAdapter->getLanguages();

        return new Response($this->twig->render(
            'Terminal42LeadsBundle:Backend:lead_details.html.twig',
            [
                'recordId' => $id,
                'created' => \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $formData->created),
                'form' => [
                    'title' => $formData->form_title,
                    'id' => $formData->form_id,
                    'master' => $formData->master_id === $formData->form_id ? false : [
                        'title' => $formData->master_title,
                        'id' => $formData->master_id,
                    ],
                ],
                'language' => [
                    'id' => $formData->language,
                    'label' => $languages[$formData->language],
                ],
                'member' => !$formData->member_id ? false : [
                    'name' => $formData->member_name,
                    'id' => $formData->member_id,
                ],
                'rows' => $this->getRows($id),
            ]
        ));
    }

    private function getFormData($leadId)
    {
        return Database::getInstance()->prepare("
            SELECT l.*, s.title AS form_title, f.title AS master_title, CONCAT(m.firstname, ' ', m.lastname) AS member_name
            FROM tl_lead l
            LEFT OUTER JOIN tl_form s ON l.form_id=s.id
            LEFT OUTER JOIN tl_form f ON l.master_id=f.id
            LEFT OUTER JOIN tl_member m ON l.member_id=m.id
            WHERE l.id=?
        ")->execute($leadId);
    }

    private function getRows($leadId): array
    {
        $rowData = Database::getInstance()->prepare("
            SELECT d.*, IF(ff.label IS NULL OR ff.label='', d.name, ff.label) AS name
            FROM tl_lead_data d
            LEFT OUTER JOIN tl_form_field ff ON d.master_id=ff.id
            WHERE d.pid=?
            ORDER BY d.sorting
        ")->execute($leadId);

        $rows = [];

        while ($rowData->next()) {

            $rows[] = [
                'name' => $rowData->name,
                'label' => $this->formatLabel($rowData),
                'value' => $this->formatValue($rowData),
            ];
        }

        return $rows;
    }

    private function formatLabel($row): string
    {
        $strValue = implode(', ', deserialize($row->value, true));

        if (!empty($row->label) && $row->label !== $row->value) {
            $strLabel = $row->label;
            $arrLabel = deserialize($row->label, true);

            if (!empty($arrLabel)) {
                $strLabel = implode(', ', $arrLabel);
            }

            $strValue = $strLabel;
        }

        return $strValue;
    }

    private function formatValue($row): ?string
    {
        if (empty($row->label) || $row->label === $row->value) {
            return null;
        }

        return implode(', ', deserialize($row->value, true));
    }
}
