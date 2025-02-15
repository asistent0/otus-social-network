<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws DBALException
     */
    public function findOneById(string $id): ?array
    {
        $sql = 'SELECT * FROM "post" WHERE id = :id';
        $data = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!$data) {
            return null;
        }

        return $data;
    }

    /**
     * @throws DBALException
     */
    public function save(Post $post): void
    {
        $sql = '
            INSERT INTO "post" (id, user_id, text, created_at)
            VALUES (:id, :user_id, :text, :created_at)
        ';

        $array = [
            'id' => $post->getId(),
            'user_id' => $post->getUser()->getId(),
            'text' => $post->getText(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
        $this->connection->executeStatement($sql, $array);
    }

    /**
     * @throws DBALException
     */
    public function update(string $id, string $text): void
    {
        $sql = 'UPDATE "post" SET text = :text WHERE id = :id';

        $this->connection->executeStatement($sql, [
            'text' => $text,
            'id' => $id,
        ]);
    }

    /**
     * @throws DBALException
     */
    public function remove(string $id): void
    {
        $sql = 'DELETE FROM post WHERE id = :id';
        $this->connection->executeStatement($sql, [
            'id' => $id,
        ]);
    }

    /**
     * @throws DBALException
     */
    public function list(int $offset, int $limit): array
    {
        $sql = 'SELECT * FROM "post" ORDER BY id OFFSET :offset LIMIT :limit';
        $data = $this->connection->fetchAllAssociative($sql, [
            'offset' => $offset,
            'limit' => $limit,
        ]);

        if (!$data) {
            return [];
        }

        return $data;
    }
}
