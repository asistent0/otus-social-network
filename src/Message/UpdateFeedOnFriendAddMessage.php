<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
readonly class UpdateFeedOnFriendAddMessage
{
    public function __construct(
        private string $userId,
        private string $friendId,
    ) {
    }

    public function getUserId(): string {
        return $this->userId;
    }

    public function getFriendId(): string {
        return $this->friendId;
    }
}
