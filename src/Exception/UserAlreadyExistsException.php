<?php

namespace App\Exception;

use Exception;

class UserAlreadyExistsException extends Exception
{
    public function __construct(string $message = 'User already exists', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
