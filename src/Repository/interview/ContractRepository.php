<?php

namespace App\Repository\interview;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }

    /**
     * Récupère tous les contrats avec pagination et filtres.
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
        $qb = $this->createQueryBuilder('c');

        if ($searchText) {
            $qb->andWhere('c.candidateName LIKE :search OR c.companyName LIKE :search')
               ->setParameter('search', '%' . $searchText . '%');
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        if ($dateFrom) {
            $qb->andWhere('c.startDate >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('c.startDate <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->orderBy('c.startDate', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Compte les contrats par statut.
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.idContrat)')
            ->andWhere('c.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les contrats signés pour une période.
     */
    public function findSignedByDateRange(\DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.signedAt >= :dateFrom')
            ->andWhere('c.signedAt <= :dateTo')
            ->andWhere('c.status = :status')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->setParameter('status', 'SIGNED')
            ->orderBy('c.signedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
