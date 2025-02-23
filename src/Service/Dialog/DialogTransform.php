<?php

namespace App\Service\Dialog;

readonly class DialogTransform
{
    public function getInfo(array $message, string $to): array
    {
        return [
            'from' => $message['sender_id'],
            'to' => $to,
            'text' => $message['text'],
        ];
    }
}
