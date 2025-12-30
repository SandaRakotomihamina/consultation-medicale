<?php

namespace App\Repository;

use App\Entity\AdresseOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdresseOption>
 */
class AdresseOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdresseOption::class);
    }

    public function findByValue(string $value): ?AdresseOption
    {
        return $this->findOneBy(['value' => $value]);
    }
}
