<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class AddToFeedMessage
{
    public function __construct(
        private string $userId,
        private string $postId
    ) {
    }

    // Геттеры
    public function getUserId(): string
    {
        return $this->userId;
    }
    public function getPostId(): string
    {
        return $this->postId;
    }
}
