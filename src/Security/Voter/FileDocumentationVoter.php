<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FileDocumentationVoter extends Voter
{
    public const VIEW = 'DOCUMENTATION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Documentation\DocFile;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                if ($subject->getGroupsCanSee() === null || count($subject->getGroupsCanSee()) === 0 || in_array('ROLE_ADMIN', $user->getRoles())) {
                    return true;
                } elseif (array_intersect($subject->getGroupsCanSee(), $user->getRoles()))
                    return true;
                break;
        }

        return false;
    }
}
