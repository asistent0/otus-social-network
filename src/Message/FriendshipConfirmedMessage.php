<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('friendship')]
readonly class FriendshipConfirmedMessage
{
    public function __construct(
        private Uuid $userId,
        private Uuid $friendId
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }
    public function getFriendId(): Uuid
    {
        return $this->friendId;
    }
}
