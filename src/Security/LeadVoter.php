<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Security;

use Contao\BackendUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class LeadVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return 'lead_form' === $attribute && is_numeric($subject);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof BackendUser || !$this->supports($attribute, $subject)) {
            return false;
        }

        if ($user->hasAccess($subject, 'forms')) {
            return true;
        }

        return false;
    }
}
