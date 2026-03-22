<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }


    public function findCategoryWithMostPublishedPosts(): ?Category
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.posts', 'p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_PUBLISHED)
            ->groupBy('c.id')
            ->orderBy('COUNT(p.id)', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
