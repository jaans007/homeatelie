<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostView;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostView::class);
    }

    public function hasRecentView(
        Post $post,
        ?User $user,
        ?string $sessionId,
        \DateTimeImmutable $since
    ): bool {
        $qb = $this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->andWhere('pv.post = :post')
            ->andWhere('pv.viewedAt >= :since')
            ->setParameter('post', $post)
            ->setParameter('since', $since);

        if ($user instanceof User) {
            $qb
                ->andWhere('pv.user = :user')
                ->setParameter('user', $user);
        } else {
            if (!$sessionId) {
                return false;
            }

            $qb
                ->andWhere('pv.sessionId = :sessionId')
                ->setParameter('sessionId', $sessionId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}



