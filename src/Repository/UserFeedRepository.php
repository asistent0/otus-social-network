<?php

namespace App\Repository;

use App\Entity\UserFeed;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<UserFeed>
 */
class UserFeedRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, UserFeed::class);
    }

    /**
     * @param array $userIds
     * @param $postId
     * @param DateTimeInterface $createdAt
     * @return void
     * @throws DBALException
     */
    public function addPostsToFeeds(
        array $userIds,
              $postId,
        DateTimeInterface $createdAt
    ): void {
        $batchSize = 100;

        $this->connection->beginTransaction();
        try {
            foreach (array_chunk($userIds, $batchSize) as $chunk) {
                $values = [];
                foreach ($chunk as $userId) {
                    $values[] = sprintf(
                        "(%s, %s, '%s')",
                        $this->connection->quote($userId),
                        $this->connection->quote($postId),
                        $createdAt->format('Y-m-d H:i:s')
                    );
                }

                $sql = sprintf(
                    'INSERT INTO user_feed (user_id, post_id, created_at) 
                     VALUES %s 
                     ON CONFLICT (user_id, post_id) DO NOTHING',
                    implode(',', $values)
                );

                $this->connection->executeStatement($sql);
            }
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
