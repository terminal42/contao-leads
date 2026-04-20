<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security\Voter;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Contracts\Service\ResetInterface;

class LeadDataAccessVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private array $formCache = [];

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly Connection $connection,
    ) {
    }

    public function reset(): void
    {
        $this->formCache = [];
    }

    protected function getTable(): string
    {
        return 'tl_lead_data';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        // Cannot add lead data through the backend
        if ($action instanceof CreateAction || $action instanceof DeleteAction) {
            return false;
        }

        // Cannot move lead data to another load
        if (
            $action instanceof UpdateAction
            && (
                \array_key_exists('sorting', $action->getNew() ?? [])
                || (
                    null !== $action->getNewPid()
                    && $action->getCurrentPid() !== $action->getNewPid()
                )
            )
        ) {
            return false;
        }

        if (!isset($this->formCache[$action->getCurrentPid()])) {
            $this->formCache[$action->getCurrentPid()] = $this->connection->fetchOne('SELECT main_id FROM tl_lead WHERE id=?', [$action->getCurrentPid()]);
        }

        return $this->accessDecisionManager->decide($token, [ContaoCorePermissions::USER_CAN_EDIT_FORM], $this->formCache[$action->getCurrentPid()]);
    }
}
