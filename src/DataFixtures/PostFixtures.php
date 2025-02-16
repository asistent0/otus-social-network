<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');
        $userRepository = $manager->getRepository(User::class);
        $batchSize = 1000;
        $totalUsers = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        for ($offset = 1; $offset < $totalUsers - 1; $offset += $batchSize) {
            $users = $userRepository->createQueryBuilder('u')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();

            foreach ($users as $user) {
                for ($j = 0; $j < mt_rand(1, 10); $j++) {
                    $post = new Post()
                        ->setUser($user)
                        ->setText($faker->realText($faker->numberBetween(100, 1000)))
                        ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
                    $manager->persist($post);
                    var_dump('add post ' . ($j + 1) . ' for user ' . $offset);
                }
            }

            $manager->flush();
            $manager->clear();
        }

        $manager->flush();
    }
}
