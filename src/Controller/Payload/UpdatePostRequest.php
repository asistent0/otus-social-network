<?php

namespace App\Controller\Payload;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdatePostRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $id,
        #[Assert\NotBlank]
        public string $text,
    ) {
    }
}
