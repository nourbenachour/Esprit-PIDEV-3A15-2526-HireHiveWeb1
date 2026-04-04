<?php

namespace App\Repository\Post;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
    public function findAllOrderedByDate(?string $keyword = null, string $sort = 'newest'): array
    {
        $postIds = $this->findOrderedPostIds($keyword, $sort);

        return $this->findHydratedPostsByIds($postIds);
    }

    /**
     * Get posts visible for the frontoffice feed, filtered by user role.
     * @return Post[]
     */
    public function findFeedPosts(?string $userRole = null, ?string $keyword = null, string $sort = 'newest'): array
    {
        $postIds = $this->findOrderedFeedPostIds($userRole, $keyword, $sort);

        return $this->findHydratedPostsByIds($postIds);
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

    /**
     * @return string[]
     */
    private function findOrderedPostIds(?string $keyword, string $sort): array
    {
        $qb = $this->createPostIdsQueryBuilder($keyword, $sort);

        return array_map(
            static fn (array $row): string => (string) $row['post_id'],
            $qb->getQuery()->getArrayResult()
        );
    }

    /**
     * @return string[]
     */
    private function findOrderedFeedPostIds(?string $userRole, ?string $keyword, string $sort): array
    {
        $qb = $this->createPostIdsQueryBuilder($keyword, $sort)
            ->andWhere('p.is_published = :pub')
            ->setParameter('pub', true);

        if ($userRole !== null && $userRole !== '') {
            $roleUpper = strtoupper($userRole);

            $qb
                ->andWhere('p.visibility = :pub_vis OR p.visibility = :role_vis')
                ->setParameter('pub_vis', 'PUBLIC')
                ->setParameter('role_vis', $roleUpper === 'RECRUITER' ? 'RECRUITER' : 'CANDIDAT');
        }

        return array_map(
            static fn (array $row): string => (string) $row['post_id'],
            $qb->getQuery()->getArrayResult()
        );
    }

    private function createPostIdsQueryBuilder(?string $keyword, string $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id_post AS post_id')
            ->leftJoin('p.comments', 'comment_sort')
            ->leftJoin('p.reactions', 'reaction_sort')
            ->addSelect('COUNT(DISTINCT comment_sort.id_comment) AS HIDDEN comments_count')
            ->addSelect('COUNT(DISTINCT reaction_sort.id_reaction) AS HIDDEN reactions_count')
            ->groupBy('p.id_post');

        $this->applySearchFilter($qb, $keyword);
        $this->applySort($qb, $sort);

        return $qb;
    }

    private function applySearchFilter(QueryBuilder $qb, ?string $keyword): void
    {
        $normalizedKeyword = trim((string) $keyword);
        if ($normalizedKeyword === '') {
            return;
        }

        $qb
            ->andWhere(
                'LOWER(COALESCE(p.title, \'\')) LIKE :keyword
                OR LOWER(COALESCE(p.content, \'\')) LIKE :keyword'
            )
            ->setParameter('keyword', '%' . mb_strtolower($normalizedKeyword) . '%');
    }

    private function applySort(QueryBuilder $qb, string $sort): void
    {
        switch ($sort) {
            case 'oldest':
                $qb->orderBy('p.created_at', 'ASC');
                break;

            case 'title-asc':
                $qb->orderBy('p.title', 'ASC')
                    ->addOrderBy('p.created_at', 'DESC');
                break;

            case 'title-desc':
                $qb->orderBy('p.title', 'DESC')
                    ->addOrderBy('p.created_at', 'DESC');
                break;

            case 'comments-desc':
                $qb->orderBy('comments_count', 'DESC')
                    ->addOrderBy('p.created_at', 'DESC');
                break;

            case 'reactions-desc':
                $qb->orderBy('reactions_count', 'DESC')
                    ->addOrderBy('p.created_at', 'DESC');
                break;

            case 'newest':
            default:
                $qb->orderBy('p.created_at', 'DESC');
                break;
        }
    }

    /**
     * @param string[] $postIds
     * @return Post[]
     */
    private function findHydratedPostsByIds(array $postIds): array
    {
        if ($postIds === []) {
            return [];
        }

        $posts = $this->createQueryBuilder('p')
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
            ->andWhere('p.id_post IN (:ids)')
            ->setParameter('ids', $postIds)
            ->distinct()
            ->getQuery()
            ->getResult();

        $postsById = [];
        foreach ($posts as $post) {
            $postsById[(string) $post->getIdPost()] = $post;
        }

        $orderedPosts = [];
        foreach ($postIds as $postId) {
            if (isset($postsById[$postId])) {
                $orderedPosts[] = $postsById[$postId];
            }
        }

        return $orderedPosts;
    }
}
