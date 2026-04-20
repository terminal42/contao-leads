<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security;

use Contao\BackendUser;
use Contao\CoreBundle\Security\Voter\AbstractBackendAccessVoter;

class LegacyBackendAccessVoter extends AbstractBackendAccessVoter
{
    #[\Override]
    public function supportsAttribute(string $attribute): bool
    {
        return str_starts_with($attribute, 'contao_user.leadp');
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute);
    }

    /**
     * Checks the user permissions against a field in tl_user(_group).
     */
    protected function hasAccess(array|null $subject, string $field, BackendUser $user): bool
    {
        trigger_deprecation('terminal42/contao-leads', '3.3', 'Checking access on "contao_user.leadp" is deprecated and will no longer work in Leads 4. Vote on "contao_user.cud" instead.');

        if (null === $subject) {
            return \count(preg_grep('/^tl_lead::/', $user->cud)) > 0;
        }

        $subject = array_map(
            static fn ($v) => match ($v) {
                'edit' => 'tl_lead_data::update',
                'delete' => 'tl_lead::delete',
                default => throw new \RuntimeException('Unsupported "contao_user.leadp" permission.'),
            },
            $subject,
        );

        return \is_array($user->cud) && array_intersect($subject, $user->cud);
    }
}
