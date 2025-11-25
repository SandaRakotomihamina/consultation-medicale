<?php

namespace App\DataFixtures;

use App\Entity\Personnel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonnelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Générer 50 personnels avec des données aléatoires simples
        $grades = [
            'Caporal', 'Sergent', 'Lieutenant', 'Capitaine', 'Commandant', 'Colonel',
            'Général de brigade', 'Général de division', 'Général de corps d\'armée', 'Général d\'armée'
        ];
        $firstNames = [
            'Rasoa', 'Rabe', 'Ravo', 'Andry', 'Hery', 'Lala', 'Miora', 'Tiana', 'Nono', 'Faly',
            'Jean', 'Pierre', 'Marie', 'Luc', 'Sophie', 'Paul', 'Julie', 'Michel', 'Nina', 'David',
            'Alice', 'Bob', 'Chloe', 'David', 'Eva', 'Frank', 'Grace', 'Hannah', 'Ian', 'Jack'
        ];
        $lastNames = [
            'RAKOTONDRABE', 'ANDRIANARIVO', 'RANDRIANASOLO', 'RAKOTOARIMANANA', 'RABEMANANJARA',
            'ANDRIAMANJATO', 'RAKOTOMALALA', 'RANDRIAMAMPIONONA', 'RABEARIMANANA', 'ANDRIANJAKA', 
            'RAKOTOVAO', 'RANDRIATSARAFARA', 'RABENIRINA', 'ANDRIANASOLO', 'RAKOTOBE',
            'RANDRIANARIMANANA', 'RABEMANJAKA', 'ANDRIANJATO', 'RAKOTOMANGA', 'RANDRIAMPIANINA'
        ];

        for ($i = 1; $i <= 100; $i++) {
            $personnel = new Personnel();

            $mat = sprintf('%05d', $i);
            $personnel->setMatricule((string)$mat);

            $grade = $grades[array_rand($grades)];
            $nom = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

            $personnel->setGrade($grade);
            $personnel->setNom($nom);

            $manager->persist($personnel);
        }

        $manager->flush();
    }
}
