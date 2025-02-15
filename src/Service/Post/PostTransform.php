<?php

namespace App\Service\Post;

readonly class PostTransform
{
    public function getInfo(array $post): array
    {
        return [
            'id' => $post['id'],
            'text' => $post['text'],
            'author_user_id' => $post['user_id'],
        ];
    }
}
