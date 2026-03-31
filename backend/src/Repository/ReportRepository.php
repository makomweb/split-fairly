<?php

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @return Report[]
     */
    public function findOlderThan(\DateTimeInterface $date): array
    {
        /** @var Report[] $result */
        $result = $this->createQueryBuilder('r')
            ->andWhere('r.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
