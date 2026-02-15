<?php

namespace App\EventListener;

use App\Repository\User\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

readonly class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthTokenRepository $authTokenRepository,
        private EntityManagerInterface $manager
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $bearerToken = $event->getRequest()->headers->get('Authorization');
        if ($bearerToken) {
            $token = explode(' ', $bearerToken)[1];
            $accessToken = $this->authTokenRepository->findOneBy([
                'token' => $token
            ]);
            $this->manager->remove($accessToken);
            $this->manager->flush();
        }

        $event->setResponse($event->getResponse());
    }
}