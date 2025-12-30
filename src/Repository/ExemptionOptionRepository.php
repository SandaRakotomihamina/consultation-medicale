<?php

namespace App\Repository;

use App\Entity\ExemptionOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExemptionOption>
 */
class ExemptionOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExemptionOption::class);
    }

    public function findByValue(string $value): ?ExemptionOption
    {
        return $this->findOneBy(['value' => $value]);
    }
}
