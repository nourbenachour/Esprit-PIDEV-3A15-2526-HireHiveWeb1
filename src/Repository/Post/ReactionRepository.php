<?php

namespace App\Repository\Post;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reaction>
 */
class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    public function add(Reaction $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Reaction $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Find the existing reaction of a user on a post (if any).
     */
    public function findUserReactionOnPost(int $userId, int $postId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.post', 'p')
            ->andWhere('u.id_user = :uid')
            ->andWhere('p.id_post = :pid')
            ->setParameter('uid', $userId)
            ->setParameter('pid', $postId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count reactions by type for a post.
     * @return array<string, int>
     */
    public function countByPost(int $postId): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('r.reaction_type AS type, COUNT(r.id_reaction) AS cnt')
            ->leftJoin('r.post', 'p')
            ->andWhere('p.id_post = :pid')
            ->setParameter('pid', $postId)
            ->groupBy('r.reaction_type')
            ->getQuery()
            ->getScalarResult();

        $counts = ['LIKE' => 0, 'LOVE' => 0, 'DISLIKE' => 0];
        foreach ($rows as $row) {
            $counts[$row['type']] = (int) $row['cnt'];
        }
        return $counts;
    }
}
