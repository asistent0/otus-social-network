<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, Message::class);
    }

    /**
     * @throws DBALException
     */
    public function save(Message $message, int $dialogId): void
    {
        $sql = '
            INSERT INTO "message" (dialog_id, sender_id, text, created_at)
            VALUES (:dialog_id, :sender_id, :text, :created_at) RETURNING id
        ';

        $array = [
            'dialog_id' => $dialogId,
            'sender_id' => $message->getSender()->getId(),
            'text' => $message->getText(),
            'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
        $this->connection->executeStatement($sql, $array);
    }

    /**
     * @throws DBALException
     */
    public function findMessagesBetweenUsers(User $user1, User $user2): ?array
    {
        $minId = min($user1->getId(), $user2->getId());
        $maxId = max($user1->getId(), $user2->getId());

        $sql = 'SELECT "message".* FROM "message" JOIN "dialog" ON "dialog"."id" = "message"."dialog_id"
         AND "dialog"."participant1_id" = :minId AND "dialog"."participant2_id" = :maxId
         ORDER BY "message"."created_at" DESC';
        $data = $this->connection->fetchAllAssociative($sql, ['minId' => $minId, 'maxId' => $maxId]);

        if (!$data) {
            return null;
        }

        return $data;
    }
}
