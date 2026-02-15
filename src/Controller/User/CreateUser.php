<?php

namespace App\Controller\User;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class CreateUser extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function __invoke(User $user): User
    {
        if ($user->getPlainPassword()) {
            $password = $this->hasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setPlainPassword(null);
        }

        return $user;
    }
}
