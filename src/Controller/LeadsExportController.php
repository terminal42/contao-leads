<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Controller;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Terminal42\LeadsBundle\Export\ExporterInterface;

#[Route(
    path: '%contao.backend.route_prefix%/leads-export/{id}',
    name: 'terminal42_leads_export',
    requirements: ['id' => '\d+'],
    defaults: ['_scope' => 'backend'],
)]
class LeadsExportController extends AbstractController
{
    /**
     * @param ServiceLocator<ExporterInterface> $exporters
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ServiceLocator $exporters,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $config = $this->connection->fetchAssociative('SELECT * FROM tl_lead_export WHERE id=?', [$id]);

        if (false === $config) {
            throw $this->createNotFoundException('Leads export ID "'.$id.'" not found.');
        }

        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'lead');
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_FORM, $config['pid']);

        if (!$this->exporters->has($config['type'])) {
            throw $this->createNotFoundException('Leads export type "'.$config['type'].'" not found.');
        }

        return $this->exporters->get($config['type'])->getResponse($config);
    }
}
