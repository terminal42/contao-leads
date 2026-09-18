<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Controller;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\LeadsBundle\Security\Terminal42LeadsPermissions;

#[Route(
    path: '%contao.backend.route_prefix%/leads-delete-all/{id}',
    name: 'terminal42_leads_delete_all',
    requirements: ['id' => '\d+'],
    defaults: ['_scope' => 'backend'],
)]
class LeadsDeleteAllController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $form = $this->connection->fetchAssociative('SELECT * FROM tl_form WHERE id=? AND leadEnabled=?', [$id, 1]);

        if (false === $form) {
            throw $this->createNotFoundException(sprintf('Form ID "%s" not found.', $id));
        }

        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'lead');
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $form['id']);
        $this->denyAccessUnlessGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_LEADS);
        $this->denyAccessUnlessGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_ALL_LEADS);

        $leadsCount = $this->connection->fetchOne('SELECT COUNT(*) FROM tl_lead WHERE form_id=?', [$form['id']]);

        if (0 === $leadsCount) {
            throw $this->createNotFoundException(sprintf('No leads found for form ID "%s".', $id));
        }

        if ($request->request->getString('FORM_SUBMIT') === 'lead_delete_all') {
            return $this->handleFormSubmit($request, $form);
        }

        $template = new BackendTemplate('backend/lead_delete_all');
        $template->explain = $this->translator->trans('tl_lead.deleteAllExplain', [], 'contao_tl_lead');
        $template->cancel = $this->translator->trans('MSC.cancelBT', [], 'contao_default');
        $template->continue = $this->translator->trans('MSC.delete', [], 'contao_default');
        $template->theme = Backend::getTheme();
        $template->language = $GLOBALS['TL_LANGUAGE'];
        $template->h1 = $this->translator->trans('tl_lead.deleteAllTitle', [], 'contao_tl_lead');
        $template->title = StringUtil::specialchars($this->translator->trans('tl_lead.deleteAllTitle', [], 'contao_tl_lead'));
        $template->host = Backend::getDecodedHostname();
        $template->charset = System::getContainer()->getParameter('kernel.charset');
        $template->info = [
            $this->translator->trans('tl_lead.deleteAllFormId', [], 'contao_tl_lead') => $form['id'],
            $this->translator->trans('tl_lead.deleteAllFormTitle', [], 'contao_tl_lead') => $form['title'],
            $this->translator->trans('tl_lead.deleteAllLeadsCount', [], 'contao_tl_lead') => $leadsCount,
        ];

        return $template->getResponse();
    }

    private function handleFormSubmit(Request $request, array $form): Response
    {
        if ($request->request->has('delete')) {
            Controller::loadDataContainer('tl_lead');
            $driverClass = DataContainer::getDriverForTable('tl_lead');
            $dc = new $driverClass('tl_lead');

            if (!$dc instanceof DC_Table) {
                throw new \RuntimeException(sprintf('The data container driver "%s" must implement "%s".', $driverClass, DC_Table::class));
            }

            $leadIds = $this->connection->fetchFirstColumn('SELECT id FROM tl_lead WHERE form_id=?', [$form['id']]);

            foreach ($leadIds as $leadId) {
                $dc->id = $leadId;
                $dc->delete(true);
            }

            Message::addConfirmation($this->translator->trans('tl_lead.deleteAllConfirm', [count($leadIds)], 'contao_tl_lead'));
        }

        return $this->redirectToRoute('contao_backend', ['do' => 'lead', 'form' => $form['id']]);
    }
}
