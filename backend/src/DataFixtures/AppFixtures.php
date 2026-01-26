<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Invariant\Ensure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        #[Autowire('%admin.email%')]
        private string $adminEmail,
        #[Autowire('%admin.password%')]
        private string $adminPassword,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        Ensure::that(!empty($adminEmail));
        Ensure::that(!empty($adminPassword));
    }

    public function load(ObjectManager $manager): void
    {
        $user = User::create($this->adminEmail, ['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $this->adminPassword);
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
