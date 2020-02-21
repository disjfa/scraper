<?php

namespace App\Repository;

use App\Entity\Scrape;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Scrape|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scrape|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scrape[]    findAll()
 * @method Scrape[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScrapeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scrape::class);
    }

    public function findPaginated()
    {
        $qb = $this->createQueryBuilder('scrape');
        $qb->orderBy('scrape.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
