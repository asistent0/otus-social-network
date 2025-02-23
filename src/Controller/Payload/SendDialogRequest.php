<?php

namespace App\Controller\Payload;

use Symfony\Component\Validator\Constraints as Assert;

readonly class SendDialogRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $text,
    ) {
    }
}
