<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Controller;

use Contao\Controller;
use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Message;
use Doctrine\DBAL\Connection;
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
class LeadsDeleteAllController extends AbstractBackendController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'lead');
        $this->denyAccessUnlessGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_LEADS);
        $this->denyAccessUnlessGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_ALL_LEADS);
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $id);

        $leadsCount = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_lead WHERE form_id = ?', [$id]);

        if (0 === $leadsCount) {
            throw $this->createNotFoundException(\sprintf('No leads found for form ID "%s".', $id));
        }

        if ('lead_delete_all' === $request->request->getString('FORM_SUBMIT')) {
            return $this->handleFormSubmit($id);
        }

        return $this->render('@Contao/backend/terminal42_leads/delete_all.html.twig', [
            'form_id' => $id,
            'leads_count' => $leadsCount,
        ]);
    }

    private function handleFormSubmit(int $formId): Response
    {
        Controller::loadDataContainer('tl_lead');
        $driverClass = DataContainer::getDriverForTable('tl_lead');
        $dc = new $driverClass('tl_lead');

        if (!$dc instanceof DC_Table) {
            throw new \RuntimeException(\sprintf('The data container driver "%s" must implement "%s".', $driverClass, DC_Table::class));
        }

        $leadIds = $this->connection->fetchFirstColumn('SELECT id FROM tl_lead WHERE form_id=?', [$formId]);

        foreach ($leadIds as $leadId) {
            $dc->id = $leadId;

            try {
                $dc->delete(true);
            } catch (AccessDeniedException) {
                continue;
            }
        }

        Message::addConfirmation($this->translator->trans('tl_lead.deleteAll.confirm', [\count($leadIds)], 'contao_tl_lead'));

        return $this->redirectToRoute('contao_backend', ['do' => 'lead', 'form' => $formId]);
    }
}
