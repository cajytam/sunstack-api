<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class AuthService
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function logout(?Request $request = null): void
    {
        $request = $request ?: new Request();
        $this->eventDispatcher->dispatch(new LogoutEvent($request, $this->tokenStorage->getToken()));
        $request->getSession()->invalidate();
    }
}