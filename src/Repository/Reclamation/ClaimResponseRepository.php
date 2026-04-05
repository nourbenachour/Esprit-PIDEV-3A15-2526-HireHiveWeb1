<?php

namespace App\Repository\Reclamation;

use App\Entity\Claim_response;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ClaimResponseRepository – data access for Claim_response entities.
 *
 * findByClaimId() retrieves all responses for a given claim,
 * ordered chronologically so the admin sees them in conversation order.
 */
class ClaimResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Claim_response::class);
    }

    /**
     * Returns all responses for a given claim, oldest first.
     *
     * @param string|int $claimId
     * @return Claim_response[]
     */
    public function findByClaimId(string|int $claimId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.claim_id', 'c')
            ->where('c.id_claim = :cid')
            ->setParameter('cid', $claimId)
            ->orderBy('r.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
