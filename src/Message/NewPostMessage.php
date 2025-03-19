<?php

namespace App\Message;

use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('new_post')]
readonly class NewPostMessage
{
    public function __construct(
        private Uuid $postId,
        private Uuid $userId,
        private DateTimeImmutable $createdAt,
    ) {
    }

    public function getPostId(): Uuid
    {
        return $this->postId;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
