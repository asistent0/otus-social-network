<?php

namespace App\DataFixtures;

use App\Entity\Friend;
use App\Entity\User;
use App\Service\Dialog\DialogService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class DialogFixtures extends Fixture
{
    function __construct(
        private readonly DialogService $dialogService,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');
        $userRepository = $manager->getRepository(User::class);
        $batchSize = 1000;
        $offset = 0;
        $totalUsers = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        for (; $offset < $totalUsers; $offset += $batchSize) {
            /** @var User[] $users */
            $users = $userRepository->createQueryBuilder('u')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize)
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult();

            foreach ($users as $user) {
                $friends = $user->getFriends();
                /** @var Friend $friend */
                foreach ($friends as $friend) {
                    for ($j = 0; $j < mt_rand(1, 10); $j++) {
                        $this->dialogService->createDialog(
                            $user,
                            $friend->getFriend(),
                            $faker->realText($faker->numberBetween(10, 200))
                        );
                        var_dump('add message ' . ($j + 1) . ' for user ' . $offset);
                    }
                }
            }

            $manager->flush();
            $manager->clear();
        }

        $manager->flush();
    }
}
