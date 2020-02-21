<?php

namespace App\Repository;

use App\Entity\Scrape;
use App\Entity\ScrapeUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ScrapeUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScrapeUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScrapeUrl[]    findAll()
 * @method ScrapeUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScrapeUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScrapeUrl::class);
    }

    public function findByUrl(Scrape $scrape, string $url)
    {
        return $this->findBy([
            'scrape' => $scrape,
            'url' => $url,
        ]);
    }
}
