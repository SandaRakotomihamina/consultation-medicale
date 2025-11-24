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
        $grades = ['Caporal', 'Sergent', 'Lieutenant', 'Capitaine', 'Commandant', 'Colonel'];
        $firstNames = ['Rasoa', 'Rabe', 'Rakoto', 'Andry', 'Hery', 'Lala', 'Miora', 'Tiana', 'Nono', 'Faly'];
        $lastNames = [
            'RAKOTONDRABE', 'ANDRIANARIVO', 'RANDRIANASOLO', 'RAKOTOARIMANANA', 'RABEMANANJARA',
            'ANDRIAMANJATO', 'RAKOTOMALALA', 'RANDRIAMAMPIONONA', 'RABEARIMANANA', 'ANDRIANJAKA', 
            'RAKOTOVAO', 'RANDRIATSARAFARA', 'RABENIRINA', 'ANDRIANASOLO', 'RAKOTOBE'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $personnel = new Personnel();

            // Matricule en string, par exemple "MAT-0001"
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
