<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FileSurveyVoter extends Voter
{
    public const VIEW = 'SURVEY_DOC_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Survey\FileSurvey;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                    return true;
                }
                $survey = $subject->getSurvey();
                if (!$survey || !$survey->getSimulation()) {
                    return false;
                }
                $owner = $survey->getSimulation()->getOwnedBy();
                if ($owner && $owner->getId() === $user->getId()) {
                    return true;
                }
                break;
        }

        return false;
    }
}
