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
    public function save(Message $message, int $dialogId, string $participant1Id): int
    {
        $sql = '
            INSERT INTO "message" (dialog_id, sender_id, text, created_at, participant1_id)
            VALUES (:dialog_id, :sender_id, :text, :created_at, :participant1_id) RETURNING id
        ';

        $array = [
            'dialog_id' => $dialogId,
            'sender_id' => $message->getSender()->getId(),
            'text' => $message->getText(),
            'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'participant1_id' => $participant1Id,
        ];

        return $this->connection->fetchOne($sql, $array);
    }

    /**
     * @throws DBALException
     */
    public function findMessagesBetweenUsers(User $user1, User $user2): ?array
    {
        $minId = min($user1->getId(), $user2->getId());
        $maxId = max($user1->getId(), $user2->getId());

        $sql = 'SELECT m.* 
            FROM "message" m
            JOIN "dialog" d 
              ON m.dialog_id = d.id AND m.participant1_id = d.participant1_id
            WHERE d.participant1_id = :minId AND d.participant2_id = :maxId
            ORDER BY m.created_at DESC
            LIMIT 100';

        $data = $this->connection->fetchAllAssociative($sql, ['minId' => $minId, 'maxId' => $maxId]);

        if (!$data) {
            return null;
        }

        return $data;
    }
}
