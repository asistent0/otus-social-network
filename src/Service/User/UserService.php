<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Exception as DMALException;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface          $validator,
        private UserRepository              $userRepository,
        private UserTransform               $userTransform,
    ) {}

    /**
     * @throws DMALException
     * @throws ExceptionInterface
     * @throws DateMalformedStringException
     */
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

    /**
     * @throws DMALException
     * @throws ExceptionInterface
     * @throws UserNotFoundException
     */
    public function getUser(string $id): array
    {
        $user = $this->userRepository->findOneById($id);
        if (!$user) {
            throw new UserNotFoundException();
        }

        return $this->userTransform->getInfo($user);
    }

    /**
     * @throws DMALException
     * @throws ExceptionInterface
     */
    public function search(string $firstName, string $lastName): array
    {
        $users = $this->userRepository->search(mb_strtolower($firstName), mb_strtolower($lastName));
        $data = [];

        foreach ($users as $user) {
            $data[] = $this->userTransform->getInfo($user);
        }

        return $data;
    }
}
