<?php

namespace App\Repository\Reclamation;

use App\Entity\Claim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ClaimRepository – custom DQL queries for the Claim entity.
 *
 * Feature 1 (Stats Dashboard):
 *   countByStatus() runs a DQL GROUP BY query and returns an array like
 *   ['OPEN' => 12, 'RESOLVED' => 4, ...] for the admin statistics dashboard.
 */
class ClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Claim::class);
    }

    /**
     * Returns all claims belonging to a specific user (identified by user_id int FK).
     *
     * Used by the front-office ClaimController to display only the logged-in
     * user's claims, not everyone's.
     *
     * @param string|int $userId  The user's DB id (id_user)
     * @return Claim[]
     */
    public function findByUserId(string|int $userId): array
    {
        $limit = new \DateTime('-48 hours');

        return $this->createQueryBuilder('c')
            ->where('c.user_id = :uid')
            ->andWhere('c.status != :resolved OR c.resolved_at >= :limit OR (c.resolved_at IS NULL AND c.created_at >= :limit)')
            ->setParameter('uid', $userId)
            ->setParameter('resolved', 'RESOLVED')
            ->setParameter('limit', $limit)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Feature 1 – Statistics Dashboard.
     *
     * Runs a DQL GROUP BY query to count how many Claims exist per status.
     * Returns an associative array: ['OPEN' => 7, 'IN_PROGRESS' => 2, 'RESOLVED' => 5, ...]
     *
     * DQL explanation:
     *   SELECT c.status, COUNT(c.id_claim) AS cnt
     *   FROM App\Entity\Claim c
     *   GROUP BY c.status
     *
     * The result is then re-indexed by status label for easy use in Twig.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.status AS status, COUNT(c.id_claim) AS cnt')
            ->groupBy('c.status')
            ->getQuery()
            ->getResult();

        // Re-index: ['OPEN' => 7, 'RESOLVED' => 3, ...]
        $stats = [];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
        }

        return $stats;
    }

    /**
     * Returns all claims ordered by creation date descending (newest first).
     * Used by the admin back-office list view.
     *
     * @return Claim[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Feature: Filtrage/Recherche multi-critères Ajax
     *
     * @return Claim[]
     */
    public function searchMultiCriteria(?string $search, ?string $status, ?string $priority): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($search)) {
            $qb->andWhere('c.title LIKE :search OR c.description LIKE :search OR c.type LIKE :search OR c.user_id LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($status)) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $status);
        }

        if (!empty($priority)) {
            $qb->andWhere('c.priority = :priority')
               ->setParameter('priority', $priority);
        }

        $qb->orderBy('c.created_at', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
