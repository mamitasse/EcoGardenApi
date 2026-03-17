<?php

namespace App\DataFixtures;

use App\Entity\Conseil;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // =========================
        // 1) Admin
        // =========================
        $admin = new User();
        $admin->setEmail('admin@ecogarden.test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCityName('Paris');
        $admin->setCreatedAt(new \DateTimeImmutable());

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'Admin123!')
        );

        $manager->persist($admin);

        // =========================
        // 2) Users
        // =========================
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_USER']);
            $user->setCityName($faker->city());
            $user->setCreatedAt(new \DateTimeImmutable());

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'User123!')
            );

            $manager->persist($user);
        }

        // =========================
        // 3) Conseils
        // =========================
        for ($i = 0; $i < 10; $i++) {
            $conseil = new Conseil();
            $conseil->setContent($faker->paragraph(2));

            // Exemple: [3] ou [4,5] ou [1,2,12]
            $monthsCount = $faker->numberBetween(1, 4);
            $months = $faker->randomElements(range(1, 12), $monthsCount);
            sort($months);

            $conseil->setMonths($months);
            $conseil->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($conseil);
        }

        $manager->flush();
    }
}