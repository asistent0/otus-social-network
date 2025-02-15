<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
        private readonly DenormalizerInterface&NormalizerInterface $serializer
    ) {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function findOneById(string $id): ?User
    {
        $sql = 'SELECT * FROM "user" WHERE id = :id';
        $data = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!$data) {
            return null;
        }

        return $this->serializer->denormalize($data, User::class);
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function save(User $user): void
    {
        $sql = '
            INSERT INTO "user" (id, role, password, first_name, last_name, birth_date, gender, biography, city)
            VALUES (:id, :role, :password, :first_name, :last_name, :birth_date, :gender, :biography, :city)
        ';

        $array = $this->serializer->normalize($user, 'json');
        $this->connection->executeStatement($sql, $array);
    }

    /**
     * @return User[]
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function search(string $firstName, string $lastName): array
    {
        $sql = 'SELECT * FROM "user" WHERE LOWER(first_name) LIKE :firstName AND LOWER(last_name) LIKE :firstName ORDER BY id';
        $data = $this->connection->fetchAllAssociative($sql, [
            'firstName' => mb_strtolower($firstName).'%',
            'lastName' => mb_strtolower($lastName).'%'
        ]);

        if (!$data) {
            return [];
        }

        return $this->serializer->denormalize($data, User::class.'[]');
    }

    /**
     * @return User[]
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function getFriends(User $user): array
    {
        $sql = 'SELECT "user".* FROM friend JOIN "user" ON "user".id = friend.friend_id WHERE friend.user_id = :id';
        $data = $this->connection->fetchAllAssociative($sql, [
            'id' => $user->getId(),
        ]);

        if (!$data) {
            return [];
        }

        return $this->serializer->denormalize($data, User::class.'[]');
    }
}
