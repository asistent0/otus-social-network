<?php

namespace App\Service\User;

class UserTransform
{

    public static function getInfo($user): array
    {
        return [
            'id' => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'birth_date' => $user->getBirthDate()->format('Y-m-d'),
            'gender' => $user->getGender()->value,
            'biography' => $user->getBiography() ?: '',
            'city' => $user->getCity() ?: '',
        ];
    }
}