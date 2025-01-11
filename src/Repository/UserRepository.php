<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\User\Gender;
use App\Service\User\Role;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    public function __construct(
        ManagerRegistry             $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOneById(string $id): ?User
    {
        $sql = 'SELECT * FROM "user" WHERE id = :id';
        $data = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->setId(Uuid::fromString($data['id']));
        $user->setRole(Role::from($data['role']));
        $user->setPassword($data['password']);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setBirthDate(new DateTimeImmutable($data['birth_date']));
        $user->setGender(Gender::from($data['gender']));

        if ($data['biography'] !== null) {
            $user->setBiography($data['biography']);
        }

        if ($data['city'] !== null) {
            $user->setCity($data['city']);
        }

        return $user;
    }

    public function save(User $user): void
    {
        $sql = '
            INSERT INTO "user" (id, role, password, first_name, last_name, birth_date, gender, biography, city)
            VALUES (:id, :role, :password, :firstName, :lastName, :birthDate, :gender, :biography, :city)
        ';

        $this->connection->executeStatement($sql, [
            'id' => $user->getid()->toString(),
            'role' => $user->getRole()->value,
            'password' => $user->getPassword(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'birthDate' => $user->getBirthDate()->format('Y-m-d'),
            'gender' => $user->getGender()->value,
            'biography' => $user->getBiography(),
            'city' => $user->getCity(),
        ]);
    }
}
