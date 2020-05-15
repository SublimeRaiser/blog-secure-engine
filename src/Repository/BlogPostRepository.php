<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    /**
     * @param integer $page
     * @param integer $limit
     *
     * @return BlogPost[]
     */
    public function getPaginatedList(int $page = 1, int $limit = 5): array
    {
        return $this->createQueryBuilder('bp')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->addOrderBy('bp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return integer
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalCount(): int
    {
        return $this->createQueryBuilder('bp')
            ->select('count(bp)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
