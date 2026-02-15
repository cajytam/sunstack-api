<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\TaskSimulation;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskSimulation>
 *
 * @method TaskSimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskSimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskSimulation[]    findAll()
 * @method TaskSimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskSimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskSimulation::class);
    }

    /**
     * @param User $user
     * @return TaskSimulation[]
     */
    public function getOpenedTaskByUser(User $user): array
    {
        $roles = $user->getRoles();

        $sql = '
            SELECT t.*
            FROM task_simulation t
            WHERE t.done_at IS NULL
            AND (
                t.target_user_id = :userId
                OR EXISTS (
                    SELECT 1
                    FROM JSON_TABLE(t.target_groups, "$[*]" COLUMNS (role VARCHAR(255) PATH "$")) AS tg
                    WHERE tg.role IN (:roles)
                )
            )
            ORDER BY 
                CASE 
                    WHEN t.updated_at IS NOT NULL THEN t.updated_at 
                    ELSE t.created_at 
                END DESC
        ';

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TaskSimulation::class, 't');
        $rsm->addFieldResult('t', 'id', 'id');

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $user->getId());
        $query->setParameter('roles', $roles);

        return $this->hydrateResults($query->getArrayResult());
    }

    /**
     * @param array $results
     * @return TaskSimulation[]
     */
    private function hydrateResults(array $results): array
    {
        $taskSimulations = [];

        foreach ($results as $result) {
            $taskSimulation = $this->_em->getRepository(TaskSimulation::class)->find($result['id']);
            if ($taskSimulation) {
                $taskSimulations[] = $taskSimulation;
            }
        }

        return $taskSimulations;
    }

    /**
     * @param User $user
     * @return TaskSimulation[]
     */
    public function getTaskOpenedByUserNotClosed(User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e, simulation')
            ->leftJoin('e.simulation', 'simulation')
            ->where('e.doneAt IS NULL')
            ->andWhere('e.openedBy = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /*
     * Il s'agit des 2 précédentes requetes unies
     */
    public function getAllOpenedTasksForUser(User $user): array
    {
        $roles = $user->getRoles();
        $userId = $user->getId();

        $sql = '
        SELECT DISTINCT t.*
        FROM task_simulation t
        LEFT JOIN simulation s ON t.simulation_id = s.id
        WHERE t.done_at IS NULL
        AND s.deleted_at IS NULL
        AND (
            t.target_user_id = :userId
            OR EXISTS (
                SELECT 1
                FROM JSON_TABLE(t.target_groups, "$[*]" COLUMNS (role VARCHAR(255) PATH "$")) AS tg
                WHERE tg.role IN (:roles)
            )
            OR t.opened_by_id = :userId
        )
        ORDER BY 
            CASE 
                WHEN t.updated_at IS NOT NULL THEN t.updated_at 
                ELSE t.created_at 
            END DESC
        ';

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TaskSimulation::class, 't');
        $rsm->addFieldResult('t', 'id', 'id');
        // Ajoutez ici tous les autres champs nécessaires

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userId);
        $query->setParameter('roles', $roles);

        return $this->hydrateResults($query->getArrayResult());
    }
}
