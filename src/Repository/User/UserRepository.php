<?php

namespace App\Repository\User;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function add(Users $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Users $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Users[]
     */
    public function searchUsers(
        ?string $query,
        ?string $role,
        ?string $status,
        string $sortField = 'created_at',
        string $direction = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('u');

        if ($query !== null && $query !== '') {
            $qb
                ->andWhere('LOWER(u.email) LIKE :q OR LOWER(u.first_name) LIKE :q OR LOWER(u.last_name) LIKE :q OR LOWER(COALESCE(u.phone, \'\')) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower($query).'%');
        }

        if ($role !== null && $role !== '') {
            $qb->andWhere('UPPER(u.role) = :role')->setParameter('role', mb_strtoupper($role));
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('LOWER(u.status) = :status')->setParameter('status', mb_strtolower($status));
        }

        $allowedSorts = [
            'role'        => 'u.role',
            'status'      => 'u.status',
            'created_at'  => 'u.createdAt',
            'email'       => 'u.email',
            'first_name'  => 'u.first_name',
            'last_name'   => 'u.last_name',
        ];

        $sortColumn = $allowedSorts[$sortField] ?? $allowedSorts['created_at'];
        $direction  = mb_strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($sortColumn, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function findDistinctRoles(): array
    {
        $rows = $this->createQueryBuilder('u')
            ->select('DISTINCT u.role AS role')
            ->orderBy('u.role', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_filter(array_map(static fn($row) => $row['role'] ?? '', $rows)));
    }

    /**
     * @return string[]
     */
    public function findDistinctStatuses(): array
    {
        $rows = $this->createQueryBuilder('u')
            ->select('DISTINCT u.status AS status')
            ->orderBy('u.status', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_filter(array_map(static fn($row) => $row['status'] ?? '', $rows)));
    }
}
