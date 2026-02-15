<?php

namespace App\Security;

use App\Repository\User\AuthTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        protected AuthTokenRepository $authTokenRepository
    )
    {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $token = $this->authTokenRepository->findOneBy([
            'token' => $accessToken
        ]);

        if (!$token) {
            throw new BadCredentialsException();
        }

        if (null !== $token->getDeletedAt()) {
            throw new CustomUserMessageAuthenticationException('Token expiré');
        }

        return new UserBadge($token->getUser()->getUserIdentifier());
    }
}