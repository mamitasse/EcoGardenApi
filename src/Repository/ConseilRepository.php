<?php

namespace App\Repository;

use App\Entity\Conseil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conseil>
 */
class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    /**
     * @return Conseil[]
     */
    public function findByMonth(int $month): array
    {
        $conseils = $this->findAll();

        return array_filter($conseils, function (Conseil $conseil) use ($month) {
            return in_array($month, $conseil->getMonths(), true);
        });
    }
}