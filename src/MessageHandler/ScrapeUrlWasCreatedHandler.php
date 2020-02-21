<?php

namespace App\MessageHandler;

use App\Entity\Scrape;
use App\Entity\ScrapeUrl;
use App\Message\ScrapeUrlWasCreated;
use App\Repository\ScrapeUrlRepository;
use App\Service\ProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use DOMElement;
use Nyholm\Psr7\Uri;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ScrapeUrlWasCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ProcessorService
     */
    private $processorService;
    /**
     * @var ScrapeUrlRepository|ObjectRepository
     */
    private $scrapeUrlRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ProcessorService $processorService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->processorService = $processorService;
        $this->scrapeUrlRepository = $this->entityManager->getRepository(ScrapeUrl::class);
        $this->logger = $logger;
    }

    public function __invoke(ScrapeUrlWasCreated $scrapeUrlWasCreated)
    {
        $scrapeUrl = $this->scrapeUrlRepository->find($scrapeUrlWasCreated->getScrapeId());

        if (false === $scrapeUrl instanceof ScrapeUrl) {
            return;
        }

        $uri = new Uri($scrapeUrl->getUrl());
        $this->logger->info('Indexing ' . $uri);
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', $uri);
        } catch (HttpExceptionInterface $e) {
            $response = $e->getResponse();
        }

        $scrapeUrl->setIndexed(true);
        $scrapeUrl->setStatusCode($response->getStatusCode());
        $this->entityManager->persist($scrapeUrl);
        $this->entityManager->flush();

        $crawler = new Crawler($response->getContent());
        $links = $crawler->filter('a');
        foreach ($links as $link) {
            $this->checkLink($link, $uri, $scrapeUrl->getScrape());
        }
    }

    public function checkLink(DOMElement $link, Uri $uri, Scrape $scrape)
    {
        if (false === $link->hasAttribute('href')) {
            return;
        }

        $href = new Uri($link->getAttribute('href'));
        if (empty($href->getHost())) {
            $href = $href->withHost($uri->getHost());
        }
        if (empty($href->getScheme())) {
            $href = $href->withScheme($uri->getScheme());
        }

        if ($href->getHost() !== $uri->getHost()) {
            return;
        }

        $indexed = $this->scrapeUrlRepository->findByUrl($scrape, $href);

        if (count($indexed)) {
            return;
        }

        $scrapeUrl = new ScrapeUrl($scrape, $href);
        $this->entityManager->persist($scrapeUrl);
        $this->entityManager->flush();

        $this->processorService->handle($scrapeUrl);
    }
}
