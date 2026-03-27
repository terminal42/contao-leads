<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security;

final class Terminal42LeadsPermissions
{
    /**
     * Access is granted if the current user can edit lead data.
     */
    public const string USER_CAN_EDIT_LEAD_DATA = 'contao_user.cud.tl_lead_data::update';

    /**
     * Access is granted if the current user can delete leads.
     */
    public const string USER_CAN_DELETE_LEADS = 'contao_user.cud.tl_lead::delete';
}
