<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface          $validator,
        private UserRepository              $userRepository,
    ) {}

    public function createUser(
        string $firstName,
        string $lastName,
        string $password,
        string $birthDate,
        string $gender,
        ?string $biography,
        string $city,
    ): User {
        $gender = Gender::tryFrom($gender);
        if ($gender === null) {
            throw new InvalidArgumentException('Invalid gender value');
        }
        // Создание нового пользователя
        $user = new User()
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setBirthDate(new DateTimeImmutable($birthDate))
            ->setGender($gender)
            ->setBiography($biography)
            ->setCity($city)->setRole(Role::User);

        $password = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($password);

        // Валидация данных
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }

        // Сохранение пользователя в базе данных
        $this->userRepository->save($user);

        return $user;
    }

    public function getUser(string $id): array
    {
        $user = $this->userRepository->findOneById($id);
        if (!$user) {
            throw new UserNotFoundException();
        }

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
