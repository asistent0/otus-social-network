<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\User\Gender;
use App\Service\User\Role;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < 1_000_000; $i++) {
            $gender = $faker->randomElement([Gender::Male, Gender::Female]);
            $user = new User()
                ->setFirstName($faker->firstName($gender->value === 'm' ? 'male' : 'female'))
                ->setLastName($faker->lastName($gender->value === 'm' ? 'male' : 'female'))
                ->setBirthDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-50 years', '-18 years')))
                ->setGender($gender)
                ->setBiography($faker->realText($faker->numberBetween(50, 100)))
                ->setCity($faker->city())
                ->setRole(Role::User);

            $password = $this->passwordHasher->hashPassword($user, $faker->password());
            $user->setPassword($password);

            $manager->persist($user);

            var_dump('add user ' . $i);

            if ($i % 100 === 0) {
                $manager->flush();
                $manager->clear();
                var_dump('flush');
            }
        }

        $manager->flush();
    }
}
