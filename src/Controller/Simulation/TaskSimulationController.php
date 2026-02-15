<?php

namespace App\Controller\Simulation;

use App\Repository\Simulation\TaskSimulationRepository;
use App\Repository\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/task_simulation', name: 'app_task_simulation_')]
class TaskSimulationController extends AbstractController
{
    #[Route('/target/{idUser}', name: 'targeted_by_user')]
    public function tasksTarget(
        int                      $idUser,
        UserRepository           $userRepository,
        TaskSimulationRepository $taskSimulationRepository,
    ): JsonResponse
    {
        $user = $userRepository->find($idUser);

        $openedTasks = $taskSimulationRepository->getOpenedTaskByUser($user);

        $task = [];
        foreach ($openedTasks as $openedTask) {
            $currentTask = static::normalizeTask($openedTask, $idUser);

            $task[] = $currentTask;
        }

        return $this->json($task);
    }


    #[Route('/opened/{idUser}', name: 'opened_by_user')]
    public function tasksOpenedBy(
        int                      $idUser,
        UserRepository           $userRepository,
        TaskSimulationRepository $taskSimulationRepository,
    ): JsonResponse
    {
        $user = $userRepository->find($idUser);

        $openedTasks = $taskSimulationRepository->getTaskOpenedByUserNotClosed($user);

        $task = [];
        foreach ($openedTasks as $openedTask) {
            $currentTask = static::normalizeTask($openedTask, $idUser);

            $task[] = $currentTask;
        }

        return $this->json($task);
    }

    #[Route('/all/{idUser}', name: 'all_opened_user')]
    public function getAllOpenedTasks(
        int                      $idUser,
        UserRepository           $userRepository,
        TaskSimulationRepository $taskSimulationRepository
    ): JsonResponse
    {
        $user = $userRepository->find($idUser);

        $tasks = $taskSimulationRepository->getAllOpenedTasksForUser($user);

        $normalizedTasks = array_map(function ($task) use ($idUser) {
            return $this->normalizeTask($task, $idUser);
        }, $tasks);

        return $this->json($normalizedTasks);
    }

    private function normalizeTask($task, int $idUser): array
    {
        return [
            'customer_name' => $task->getSimulation()->getCustomerName(),
            'simulation_title' => $task->getSimulation()->getName(),
            'link' => [
                'id' => $task->getSimulation()->getId(),
                'name' => 'simulation_show',
                'query' => [
                    "tab" => "management",
                    'task' => $task->getId()
                ],
                'id_name' => 'simulationId'
            ],
            'title' => $task->getTitle(),
            'createdAt' => $task->getCreatedAt(),
            'updatedAt' => $task->getUpdatedAt(),
            'opened' => $task->getOpenedBy()->getId() === $idUser,
        ];
    }
}
