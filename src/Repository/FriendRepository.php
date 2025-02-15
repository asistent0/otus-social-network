<?php

namespace App\Repository;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friend>
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, Friend::class);
    }

    public function save(?Friend $friend = null): void
    {
        if ($friend) {
            $this->getEntityManager()->persist($friend);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @throws DBALException
     */
    public function remove(User $user, User $friend): void
    {
        $sql = 'DELETE FROM friend WHERE user_id = :user_id AND friend_id = :friend_id';
        $this->connection->executeStatement($sql, [
            'user_id' => $user->getId(),
            'friend_id' => $friend->getId(),
        ]);
    }
}
