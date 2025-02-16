<?php

namespace App\DataFixtures;

use App\Entity\Friend;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FriendFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $connection = $manager->getConnection();
        $userRepository = $manager->getRepository(User::class);
        $batchSize = 1000;
        $countFriend = mt_rand(1, 10);
        $totalUsers = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $totalUsers -= 1;
        $group = (int)round($totalUsers / 100);
        $path = 1;

        for ($offset = $group * ($path - 1) + 1; $offset < $group * $path; $offset += $batchSize) {
            $users = $userRepository->createQueryBuilder('u')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();

            $i = 0;
            foreach ($users as $user) {
                $sql = 'SELECT id FROM "user" WHERE id != :id AND id != :user_id ORDER BY random() LIMIT :limit';
                $data = $connection->fetchAllAssociative($sql, [
                    'id' => $user->getId()->toString(),
                    'user_id' => '019456d0-e928-7abf-b5d8-906186b934f9',
                    'limit' => $countFriend,
                ]);
                foreach ($data as $randomId) {
                    $randomUser = $userRepository->find($randomId['id']);
                    $friend = new Friend()
                        ->setUser($user)
                        ->setFriend($randomUser);
                    $manager->persist($friend);
                }
                var_dump('add friend ' . ($offset + $i));
                $i++;
            }

            $manager->flush();
            $manager->clear();
        }

        $manager->flush();
    }
}
