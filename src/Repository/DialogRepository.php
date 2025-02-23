<?php

namespace App\Repository;

use App\Entity\Dialog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dialog>
 */
class DialogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, Dialog::class);
    }

    /**
     * @throws DBALException
     */
    public function findDialogBetweenUsers(User $user1, User $user2): ?array
    {
        $minId = min($user1->getId(), $user2->getId());
        $maxId = max($user1->getId(), $user2->getId());


        $sql = 'SELECT * FROM "dialog" WHERE "participant1_id" = :minId AND "participant2_id" = :maxId';
        $data = $this->connection->fetchAssociative($sql, ['minId' => $minId, 'maxId' => $maxId]);

        if (!$data) {
            return null;
        }

        return $data;
    }

    /**
     * @throws DBALException
     */
    public function save(Dialog $dialog): void
    {
        $sql = '
            INSERT INTO "dialog" (participant1_id, participant2_id)
            VALUES (:participant1_id, :participant2_id) RETURNING id
        ';

        $array = [
            'participant1_id' => $dialog->getParticipant1()->getId(),
            'participant2_id' => $dialog->getParticipant2()->getId(),
        ];

        $this->connection->executeStatement($sql, $array);
    }
}
