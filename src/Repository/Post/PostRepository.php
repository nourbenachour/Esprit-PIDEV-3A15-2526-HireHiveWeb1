<?php

namespace App\Repository\Post;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function add(Post $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Get all posts ordered by date (BackOffice admin view).
     * @return Post[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.id_user', 'u')
            ->addSelect('u')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->leftJoin('c.user', 'cu')
            ->addSelect('cu')
            ->distinct()
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get posts visible for the frontoffice feed, filtered by user role.
     * @return Post[]
     */
    public function findFeedPosts(?string $userRole = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.id_user', 'u')
            ->addSelect('u')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->leftJoin('c.user', 'cu')
            ->addSelect('cu')
            ->leftJoin('p.reactions', 'r')
            ->addSelect('r')
            ->leftJoin('r.user', 'ru')
            ->addSelect('ru')
            ->andWhere('p.is_published = :pub')
            ->setParameter('pub', true)
            ->distinct()
            ->orderBy('p.created_at', 'DESC');

        if ($userRole !== null && $userRole !== '') {
            $roleUpper = strtoupper($userRole);
            // Show PUBLIC posts + posts matching user's role visibility
            $qb->andWhere('p.visibility = :pub_vis OR p.visibility = :role_vis')
               ->setParameter('pub_vis', 'PUBLIC')
               ->setParameter('role_vis', $roleUpper === 'RECRUITER' ? 'RECRUITER' : 'CANDIDAT');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get posts by a specific user.
     * @return Post[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.id_user', 'u')
            ->addSelect('u')
            ->andWhere('u.id_user = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
