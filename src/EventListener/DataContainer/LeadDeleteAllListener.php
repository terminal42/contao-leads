<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Terminal42\LeadsBundle\Security\Terminal42LeadsPermissions;

#[AsCallback('tl_lead', 'list.global_operations.deleteAll.button')]
readonly class LeadDeleteAllListener
{
    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack,
        private RouterInterface $router,
        private Security $security,
    ) {
    }

    public function __invoke(DataContainerOperation $operation): void
    {
        $formId = $this->getFormId();

        if (!$formId) {
            $operation->hide();

            return;
        }

        $operation->setUrl($this->router->generate('terminal42_leads_delete_all', ['id' => $formId]));
    }

    private function getFormId(): int|null
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $formId = $request->query->getInt('form');

        if (!$formId) {
            return null;
        }

        if (
            !$this->security->isGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_LEADS)
             || !$this->security->isGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_ALL_LEADS)
        ) {
            return null;
        }

        $hasLeads = $this->connection->fetchOne('SELECT TRUE FROM tl_lead WHERE form_id=? LIMIT 1', [$formId]);

        if (false === $hasLeads) {
            return null;
        }

        return $formId;
    }
}
