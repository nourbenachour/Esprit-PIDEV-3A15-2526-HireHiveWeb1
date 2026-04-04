<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class JobOfferRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * List all offers with optional search, status and contract_type filters.
     */
    public function findAll(string $search = '', string $status = '', string $contractType = ''): array
    {
        $qb = $this->conn->createQueryBuilder()
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
        $qb = $this->conn->createQueryBuilder()
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
        $row = $this->conn->fetchAssociative(
            'SELECT * FROM job_offer WHERE id_job_offer = :id',
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function insert(array $data): void
    {
        $this->conn->executeStatement(
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

    public function update(int $id, array $data): void
    {
        $this->conn->executeStatement(
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

    public function delete(int $id): void
    {
        // Delete related applications first (FK)
        $this->conn->executeStatement('DELETE FROM application WHERE job_offer_id = :id', ['id' => $id]);
        $this->conn->executeStatement('DELETE FROM job_offer WHERE id_job_offer = :id', ['id' => $id]);
    }
}
