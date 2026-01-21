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
        // Create a default superadmin user for development/testing
        $superadmin = new User();
        $superadmin->setUsername('superadmin');
        $superadmin->setMatricule('000000');
        $superadmin->setTitle('Dr.');
        $superadmin->setName('Superadmin');
        $superadmin->setRoles(['ROLE_SUPER_ADMIN']);

        // Hash the password using the project's configured hasher
        $hashed = $this->passwordHasher->hashPassword($superadmin, 'superadmin');
        $superadmin->setPassword($hashed);

        $manager->persist($superadmin);
        $manager->flush();

    }
}
