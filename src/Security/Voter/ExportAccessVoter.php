<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security\Voter;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Contao\CoreBundle\Security\Voter\DataContainer\ParentAccessTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExportAccessVoter extends AbstractDataContainerVoter
{
    use ParentAccessTrait;

    protected function getTable(): string
    {
        return 'tl_lead_export';
    }

    protected function hasAccess(TokenInterface $token, UpdateAction|CreateAction|ReadAction|DeleteAction $action): bool
    {
        return $this->accessDecisionManager->decide($token, [ContaoCorePermissions::USER_CAN_ACCESS_MODULE], 'form')
            && $this->accessDecisionManager->decide($token, [ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE], 'tl_lead_export')
            && $this->hasAccessToParent($token, ContaoCorePermissions::USER_CAN_EDIT_FORM, $action);
    }
}
