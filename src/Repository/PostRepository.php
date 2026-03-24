<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestPublished(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOnePublishedBySlug(string $slug): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->andWhere('p.status = :status')
            ->setParameter('category', $category)
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->setParameter('author', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDraftsByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->andWhere('p.status = :status')
            ->setParameter('author', $user)
            ->setParameter('status', Post::STATUS_DRAFT)
            ->orderBy('p.updatedAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->andWhere('p.status = :status')
            ->setParameter('author', $user)
            ->setParameter('status', Post::STATUS_PENDING)
            ->orderBy('p.updatedAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->andWhere('p.status = :status')
            ->setParameter('author', $user)
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRejectedByAuthor(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->andWhere('p.status = :status')
            ->setParameter('author', $user)
            ->setParameter('status', Post::STATUS_REJECTED)
            ->orderBy('p.updatedAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPending(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PENDING)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findHomepageFeatured(int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findMostPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findEditorPickedFallback(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult(4)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPopularPublishedByCategory(Category $category, int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.category = :category')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('category', $category)
            ->orderBy('p.viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findTrendingLast30Days(int $limit = 12): array
    {
        $since = new \DateTimeImmutable('-30 days');

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt >= :since')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('since', $since)
            ->orderBy('p.viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findTrendingLast30DaysQueryBuilder()
    {
        $since = new \DateTimeImmutable('-30 days');

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt >= :since')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('since', $since)
            ->orderBy('p.viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC');
    }

    public function findTrendingLast30DaysPaged(int $page = 1, int $limit = 8): array
    {
        $since = new \DateTimeImmutable('-30 days');
        $offset = max(0, ($page - 1) * $limit);

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt >= :since')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('since', $since)
            ->orderBy('p.viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countTrendingLast30Days(): int
    {
        $since = new \DateTimeImmutable('-30 days');

        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt >= :since')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecommendedPaged(int $page = 1, int $limit = 8): array
    {
        $offset = max(0, ($page - 1) * $limit);

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.isRecommended = :isRecommended')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('isRecommended', true)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countRecommended(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->andWhere('p.isRecommended = :isRecommended')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('isRecommended', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecommendedForHomepage(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.isRecommended = :isRecommended')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->setParameter('isRecommended', true)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
