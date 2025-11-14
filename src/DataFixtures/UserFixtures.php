<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create a default admin user for development/testing
        $user = new User();
        $user->setUsername('admin');
        $user->setTitle('Dr.');
        $user->setName('Sanda');
        $user->setRoles(['ROLE_ADMIN']);

        // Hash the password using the project's configured hasher
        $hashed = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashed);

        $manager->persist($user);
        $manager->flush();
    }
}
