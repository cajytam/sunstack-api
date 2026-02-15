<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PDFVoter extends Voter
{
    public const SIMULATION = 'PDF_SIMULATION';
    public const MANDAT = 'PDF_MANDAT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::MANDAT, self::SIMULATION])
            && $subject instanceof \App\Entity\Simulation\Simulation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::SIMULATION:
                if (in_array('ROLE_USER', $user->getRoles()))
                    return true;
                break;
            case self::MANDAT:
                if (in_array('ROLE_ADMIN', $user->getRoles()))
                    return true;
                break;
        }

        return false;
    }
}
