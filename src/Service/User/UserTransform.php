<?php

namespace App\Service\User;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

readonly class UserTransform
{
    function __construct(
        private SerializerInterface $serializer
    ) {}

    public function getInfo($user): array
    {
        return $this->serializer->normalize($user, 'json', [
            AbstractNormalizer::ATTRIBUTES => ['id', 'firstName', 'lastName', 'birthDate', 'gender', 'biography', 'city']
        ]);
    }
}