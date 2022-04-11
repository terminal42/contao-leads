<?php

/** @noinspection StaticInvocationViaThisInspection */
/** @noinspection PhpTranslationDomainInspection */
/** @noinspection PhpTranslationKeyInspection */

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Controller\Backend;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\Database;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Terminal42\LeadsBundle\Model\Lead;
use Twig\Environment;

class LeadDetailsController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var System
     */
    private $systemAdapter;

    public function __construct(ContaoFramework $framework, Environment $twig, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->framework = $framework;
        $this->twig = $twig;
        $this->authorizationChecker = $authorizationChecker;

        $this->systemAdapter = $framework->getAdapter(System::class);
    }

    /**
     * @Route("/contao/lead/{id}/show", name="terminal42_leads.details", requirements={"id"="\d+"}, defaults={"_scope"="backend"})
     */
    public function __invoke(int $id)
    {
        $this->framework->initialize();

        $lead = Lead::findByPk($id);

        if (!$lead instanceof Lead || !$this->authorizationChecker->isGranted('lead_form', $lead->master_id)) {
            $exception = new AccessDeniedException('Not enough permissions to access leads of form ID "'.$lead->master_id.'"');
            $exception->setAttributes('lead_form');
            $exception->setSubject($lead->master_id);

            throw $exception;
        }

        $formData = $this->getFormData($id);
        $languages = $this->systemAdapter->getLanguages();
        $locale = $formData->language;

        if (class_exists(LocaleUtil::class)) {
            $locale = LocaleUtil::formatAsLocale($locale);
        }

        return new Response($this->twig->render(
            '@Terminal42Leads/Backend/lead_details.html.twig',
            [
                'recordId' => $id,
                'created' => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $formData->created),
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
                    'label' => $languages[$locale] ?? $locale,
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
        $strValue = implode(', ', StringUtil::deserialize($row->value, true));

        if (!empty($row->label) && $row->label !== $row->value) {
            $strLabel = $row->label;
            $arrLabel = StringUtil::deserialize($row->label, true);

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

        return implode(', ', StringUtil::deserialize($row->value, true));
    }
}
