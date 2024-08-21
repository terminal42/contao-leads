<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Terminal42\LeadsBundle\Security\Terminal42LeadsPermissions;

#[AsCallback('tl_lead', 'config.onload')]
#[AsCallback('tl_lead_data', 'config.onload')]
class UserPermissionsListener
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function __invoke(): void
    {
        if (!empty($GLOBALS['TL_DCA']['tl_lead']) && !$this->authorizationChecker->isGranted(Terminal42LeadsPermissions::USER_CAN_DELETE_LEADS)) {
            $GLOBALS['TL_DCA']['tl_lead']['config']['notDeletable'] = true;
            unset($GLOBALS['TL_DCA']['tl_lead']['list']['operations']['delete']);
        }

        if (!empty($GLOBALS['TL_DCA']['tl_lead_data']) && !$this->authorizationChecker->isGranted(Terminal42LeadsPermissions::USER_CAN_EDIT_LEAD_DATA)) {
            $GLOBALS['TL_DCA']['tl_lead_data']['config']['notEditable'] = true;
            unset($GLOBALS['TL_DCA']['tl_lead_data']['list']['operations']['edit']);
        }
    }
}
