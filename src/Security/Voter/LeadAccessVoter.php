<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security\Voter;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class LeadAccessVoter extends AbstractDataContainerVoter
{
    public function __construct(private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function getTable(): string
    {
        return 'tl_lead';
    }

    protected function hasAccess(TokenInterface $token, UpdateAction|CreateAction|ReadAction|DeleteAction $action): bool
    {
        // Cannot create leads through the backend
        if ($action instanceof CreateAction) {
            return false;
        }

        // Cannot move a leads to another form
        if (
            $action instanceof UpdateAction
            && isset($action->getNew()['main_id'])
            && $action->getNew()['main_id'] !== ($action->getNew()['main_id'] ?? null)
        ) {
            return false;
        }

        $formId = (int) $action->getCurrent()['main_id'];

        return $this->accessDecisionManager->decide($token, [ContaoCorePermissions::USER_CAN_EDIT_FORM], $formId);
    }
}
