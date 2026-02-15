<?php

namespace App\Controller\User;

use App\Entity\User\Notification;
use App\Repository\User\NotificationRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notif', name: 'app_notifications_')]
class ManageNotifications extends AbstractController
{
    #[Route('/survey-roles', name: 'survey_roles', methods: ['POST'])]
    public function postNotificationSurveyToRoles(
        Request                $request,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $users = $userRepository->findUsersFromRoles($parameters['roles']);

        foreach ($users as $user) {
            $isAlreadyExistsAndUnread = $notificationRepository->isNotificationAlreadyExistsAndUnread(
                $user->getId(),
                $parameters['title'],
                $parameters['message'],
            );

            if (!$isAlreadyExistsAndUnread) {
                $notification = new Notification();
                $notification
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setMessage($parameters['message'])
                    ->setTitle($parameters['title'])
                    ->setUrl([
                        'name' => $parameters['urlName'],
                        'id' => $parameters['urlId'],
                        'id_name' => $parameters['urlIdName'],
                        'query' => $parameters['query']
                    ])
                    ->setUser($user);
                $entityManager->persist($notification);
            }
        }
        $entityManager->flush();

        return $this->json(
            ['success' => true]
        );
    }

    #[Route('/survey-user', name: 'survey_user', methods: ['POST'])]
    public function postNotificationSurveyToUser(
        Request                $request,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        foreach ($parameters['listOfUsers'] as $userId) {
            $isAlreadyExistsAndUnread = $notificationRepository->isNotificationAlreadyExistsAndUnread(
                $userId,
                $parameters['title'],
                $parameters['message'],
            );

            if (!$isAlreadyExistsAndUnread) {
                $user = $userRepository->find($userId);

                $notification = new Notification();
                $notification
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setMessage($parameters['message'])
                    ->setTitle($parameters['title'])
                    ->setUrl([
                        'name' => $parameters['urlName'],
                        'id' => $parameters['urlId'],
                        'id_name' => $parameters['urlIdName'],
                        'query' => $parameters['query']
                    ])
                    ->setUser($user);
                $entityManager->persist($notification);
            }
        }
        $entityManager->flush();

        return $this->json(
            ['success' => true]
        );
    }

    #[Route('/task-user', name: 'task', methods: ['POST'])]
    public function postNotificationTask(
        Request                $request,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
    ): Response
    {
        $users = [];
        $parameters = json_decode($request->getContent(), true);

        if (isset($parameters['rolesTargeted']) && count($parameters['rolesTargeted']) > 0) {
            $users = $userRepository->findUsersFromRoles($parameters['rolesTargeted']);
        }

        if (isset($parameters['usersTargeted'])) {
            if (is_string($parameters['usersTargeted'])) {
                $array = explode('/', $parameters['usersTargeted']);
                $idUser = end($array);
                $users[] = $userRepository->find($idUser);
            } else {
                foreach ($parameters['usersTargeted'] as $k => $v) {
                    if ($k === 'id') {
                        $users[] = $userRepository->find($v);
                    }
                }
            }
        }

        $taskCreator = $userRepository->find($parameters['userOpenedId']);
        if ($parameters['idUserWhichSent'] !== $parameters['userOpenedId'] && !in_array($taskCreator, $users)) {
            $users[] = $taskCreator;
        }

        foreach ($users as $user) {
            if ($user->getId() !== $parameters['idUserWhichSent']) {
                $isAlreadyExistsAndUnread = $notificationRepository->isNotificationAlreadyExistsAndUnread(
                    $user->getId(),
                    $parameters['title'],
                    $parameters['message'],
                );

                if (!$isAlreadyExistsAndUnread) {
                    $notification = new Notification();
                    $notification
                        ->setCreatedAt(new \DateTimeImmutable())
                        ->setTitle($parameters['title'])
                        ->setMessage($parameters['message'])
                        ->setUrl([
                            'name' => $parameters['urlName'],
                            'id' => $parameters['urlId'],
                            'id_name' => $parameters['urlIdName'],
                            'query' => $parameters['query']
                        ])
                        ->setUser($user)
                        ->setTypeNotif($parameters['typeNotification']);
                    $entityManager->persist($notification);
                }
            }
        }
        $entityManager->flush();

        return $this->json(
            ['success' => true]
        );
    }
}
