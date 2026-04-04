<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class ApplicationRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get all applications for a given job offer, with candidate info.
     */
    public function findByJobOffer(int $jobOfferId): array
    {
        return $this->conn->fetchAllAssociative(
            'SELECT a.*, u.first_name, u.last_name, u.email
             FROM application a
             LEFT JOIN users u ON u.id_user = a.condidat_id
             WHERE a.job_offer_id = :jid
             ORDER BY a.application_date DESC',
            ['jid' => $jobOfferId]
        );
    }

    /**
     * Get all applications by a given candidate, with offer info.
     */
    public function findByCandidate(int $candidateId): array
    {
        return $this->conn->fetchAllAssociative(
            'SELECT a.*, j.title AS offer_title, j.location, j.contract_type, j.status AS offer_status
             FROM application a
             LEFT JOIN job_offer j ON j.id_job_offer = a.job_offer_id
             WHERE a.condidat_id = :cid
             ORDER BY a.application_date DESC',
            ['cid' => $candidateId]
        );
    }

    public function findById(int $id): ?array
    {
        $row = $this->conn->fetchAssociative(
            'SELECT * FROM application WHERE id_condidature = :id',
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function hasApplied(int $candidateId, int $jobOfferId): bool
    {
        $result = $this->conn->fetchOne(
            'SELECT 1 FROM application WHERE condidat_id = :cid AND job_offer_id = :jid LIMIT 1',
            ['cid' => $candidateId, 'jid' => $jobOfferId]
        );
        return $result !== false;
    }

    public function insert(array $data): void
    {
        $this->conn->executeStatement(
            'INSERT INTO application (condidat_id, job_offer_id, cv_file_path, lettre, status, application_date, last_update)
             VALUES (:condidat_id, :job_offer_id, :cv_file_path, :lettre, :status, NOW(), NOW())',
            [
                'condidat_id'   => $data['condidat_id'],
                'job_offer_id'  => $data['job_offer_id'],
                'cv_file_path'  => $data['cv_file_path'],
                'lettre'        => $data['lettre'],
                'status'        => $data['status'] ?? 'PENDING',
            ]
        );
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->conn->executeStatement(
            'UPDATE application SET status = :status, last_update = NOW() WHERE id_condidature = :id',
            ['status' => $status, 'id' => $id]
        );
    }
}
