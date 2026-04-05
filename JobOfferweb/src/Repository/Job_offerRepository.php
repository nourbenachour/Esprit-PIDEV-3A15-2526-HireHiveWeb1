<?php

namespace App\Repository;

use App\Entity\Job_offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Job_offerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job_offer::class);
    }

    /**
     * List all offers with optional search, status and contract_type filters.
     */
    public function findAllOffers(string $search = '', string $status = '', string $contractType = ''): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $qb = $conn->createQueryBuilder()
            ->select('j.*')
            ->from('job_offer', 'j')
            ->orderBy('j.created_at', 'DESC');

        if ($search !== '') {
            $qb->andWhere('(LOWER(j.title) LIKE :q OR LOWER(j.description) LIKE :q OR LOWER(j.location) LIKE :q)')
               ->setParameter('q', '%' . mb_strtolower($search) . '%');
        }
        if ($status !== '') {
            $qb->andWhere('j.status = :status')->setParameter('status', $status);
        }
        if ($contractType !== '') {
            $qb->andWhere('j.contract_type = :ct')->setParameter('ct', $contractType);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * List OPEN offers only (for the front).
     */
    public function findOpen(string $search = '', string $contractType = ''): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $qb = $conn->createQueryBuilder()
            ->select('j.*')
            ->from('job_offer', 'j')
            ->where("j.status = 'OPEN'")
            ->orderBy('j.created_at', 'DESC');

        if ($search !== '') {
            $qb->andWhere('(LOWER(j.title) LIKE :q OR LOWER(j.description) LIKE :q OR LOWER(j.location) LIKE :q)')
               ->setParameter('q', '%' . mb_strtolower($search) . '%');
        }
        if ($contractType !== '') {
            $qb->andWhere('j.contract_type = :ct')->setParameter('ct', $contractType);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function findById(int $id): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        $row = $conn->fetchAssociative(
            'SELECT * FROM job_offer WHERE id_job_offer = :id',
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function insertOffer(array $data): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement(
            'INSERT INTO job_offer (title, description, location, contract_type, status, skills, soft_skills, recruiter_id, created_at)
             VALUES (:title, :description, :location, :contract_type, :status, :skills, :soft_skills, :recruiter_id, NOW())',
            [
                'title'         => $data['title'],
                'description'   => $data['description'],
                'location'      => $data['location'],
                'contract_type' => $data['contract_type'],
                'status'        => $data['status'],
                'skills'        => $data['skills'],
                'soft_skills'   => $data['soft_skills'] ?? '',
                'recruiter_id'  => $data['recruiter_id'],
            ]
        );
    }

    public function updateOffer(int $id, array $data): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement(
            'UPDATE job_offer SET title = :title, description = :description, location = :location,
             contract_type = :contract_type, status = :status, skills = :skills, soft_skills = :soft_skills
             WHERE id_job_offer = :id',
            [
                'title'         => $data['title'],
                'description'   => $data['description'],
                'location'      => $data['location'],
                'contract_type' => $data['contract_type'],
                'status'        => $data['status'],
                'skills'        => $data['skills'],
                'soft_skills'   => $data['soft_skills'] ?? '',
                'id'            => $id,
            ]
        );
    }

    public function deleteOffer(int $id): void
    {
        $conn = $this->getEntityManager()->getConnection();
        // Delete related applications first (FK)
        $conn->executeStatement('DELETE FROM application WHERE job_offer_id = :id', ['id' => $id]);
        $conn->executeStatement('DELETE FROM job_offer WHERE id_job_offer = :id', ['id' => $id]);
    }
}
