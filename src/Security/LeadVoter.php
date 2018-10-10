<?php

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
    protected function supports($attribute, $subject)
    {
        return 'lead_form' === $attribute && \is_numeric($subject);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof BackendUser || !$this->supports($attribute, $subject)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($user->hasAccess($subject, 'forms')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
