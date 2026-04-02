<?php

namespace App\Repository\interview;

use App\Entity\Questionnaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Questionnaire>
 */
class QuestionnaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaire::class);
    }

    /**
     * Récupère tous les questionnaires par jobTitle.
     */
    public function findByJobTitle(string $jobTitle): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.jobTitle = :jobTitle')
            ->setParameter('jobTitle', $jobTitle)
            ->orderBy('q.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les jobs titles uniques.
     */
    public function findUniqueJobTitles(): array
    {
        $result = $this->createQueryBuilder('q')
            ->select('DISTINCT q.jobTitle')
            ->orderBy('q.jobTitle', 'ASC')
            ->getQuery()
            ->getResult();

        // Flatten array of arrays to simple array
        return array_map(fn($row) => $row['jobTitle'], $result);
    }

    /**
     * Compte les questions par jobTitle.
     */
    public function countByJobTitle(string $jobTitle): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.idQuestionnaire)')
            ->andWhere('q.jobTitle = :jobTitle')
            ->setParameter('jobTitle', $jobTitle)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
