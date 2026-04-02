<?php

namespace App\Repository\interview;

use App\Entity\Interview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Interview>
 */
class InterviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Interview::class);
    }

    /**
     * Récupère tous les entretiens avec pagination et filtres.
     * 
     * @param string|null $searchText    Recherche par candidateName ou companyName
     * @param string|null $status        Filtrer par statut
     * @param \DateTimeInterface|null $dateFrom  Filtrer de cette date
     * @param \DateTimeInterface|null $dateTo    Filtrer jusqu'à cette date
     * @return array
     */
    public function findByFilters(
        ?string $searchText = null,
        ?string $status = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null
    ): array {
        $qb = $this->createQueryBuilder('i');

        if ($searchText) {
            $qb->andWhere('i.candidateName LIKE :search OR i.companyName LIKE :search')
               ->setParameter('search', '%' . $searchText . '%');
        }

        if ($status) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $status);
        }

        if ($dateFrom) {
            $qb->andWhere('i.interviewDate >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('i.interviewDate <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->orderBy('i.interviewDate', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Compte les entretiens par statut.
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.idInterview)')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les entretiens planifiés pour une période donnée.
     */
    public function findPlannedByDateRange(\DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.interviewDate >= :dateFrom')
            ->andWhere('i.interviewDate <= :dateTo')
            ->andWhere('i.attendanceStatus = :attendance')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->setParameter('attendance', 'PLANNED')
            ->orderBy('i.interviewDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
