<?php

namespace App\Service;

use App\Entity\Scrape;
use App\Entity\ScrapeUrl;
use App\Repository\ScrapeUrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use DOMElement;
use Nyholm\Psr7\Uri;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ScrapeService
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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ScrapeUrlRepository
     */
    private $scrapeUrlRepository;

    /**
     * Scrape constructor.
     * @param EntityManagerInterface $entityManager
     * @param ScrapeUrlRepository $scrapeUrlRepository
     * @param ProcessorService $processorService
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, ScrapeUrlRepository $scrapeUrlRepository, ProcessorService $processorService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->scrapeUrlRepository = $scrapeUrlRepository;
        $this->processorService = $processorService;
        $this->logger = $logger;
    }

    /**
     * @param ScrapeUrl $scrapeUrl
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrape(ScrapeUrl $scrapeUrl)
    {
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

        try {
            $crawler = new Crawler($response->getContent());
            $this->checkContent($crawler, $uri, $scrapeUrl);
        } catch (ClientException $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * @param Crawler $crawler
     * @return string|null
     */
    public function getPageTitle(Crawler $crawler)
    {
        $filters = [
            'main h1',
            '[role="main"] h1',
            'h1'
        ];

        foreach ($filters as $filter) {
            $h1 = $crawler->filter($filter);
            if ($h1->count()) {
                return $h1->first()->text();
            }
        }

        return null;
    }

    /**
     * @param Crawler $crawler
     * @return Crawler|null
     */
    public function getMainContent(Crawler $crawler)
    {
        $filters = [
            'main',
            '[role="main"]',
            'body'
        ];

        foreach ($filters as $filter) {
            $h1 = $crawler->filter($filter);
            if ($h1->count()) {
                return $h1->first();
            }
        }

        return null;
    }

    /**
     * @param Crawler $crawler
     * @param string $type
     * @return string|null
     */
    public function getOgContent(Crawler $crawler, string $type)
    {
        $og = $crawler->filter('[property="' . $type . '"]');
        if ($og->count()) {
            return $og->first()->attr('content');
        }
        return null;
    }

    public function getCanonical(Crawler $crawler)
    {
        $canonical = $crawler->filter('[rel="canonical"]');
        if ($canonical->count()) {
            return $canonical->first()->attr('href');
        }
        return null;
    }

    /**
     * @param Crawler $crawler
     * @param Uri $uri
     * @param ScrapeUrl $scrapeUrl
     */
    public function checkContent(Crawler $crawler, Uri $uri, ScrapeUrl $scrapeUrl)
    {
        $scrapeUrl->setTitle($this->getPageTitle($crawler));
        $scrapeUrl->setOgType($this->getOgContent($crawler, 'og:type'));
        $scrapeUrl->setOgTitle($this->getOgContent($crawler, 'og:title'));
        $scrapeUrl->setOgDescription($this->getOgContent($crawler, 'og:description'));
        $scrapeUrl->setOgUrl($this->getOgContent($crawler, 'og:url'));
        $scrapeUrl->setOgImage($this->getOgContent($crawler, 'og:image'));

        $contents = $this->getMainContent($crawler);
        $data = [];
        if ($contents) {
            $data = $contents->filterXPath('//text()[not(ancestor::script)]')->extract(['_text']);
            foreach ($data as $k => $line) {
                $line = preg_replace("/[\r\n]+/", "\n", $line);
                $line = preg_replace("/\s+/", ' ', $line);
                $line = trim($line);
                $data[$k] = $line;
            }
        }
        $data = array_filter($data);
        $scrapeUrl->setContent(implode(' ', $data));
        $scrapeUrl->setCanonical($this->getCanonical($crawler));

        $this->entityManager->persist($scrapeUrl);
        $this->entityManager->flush();

        $links = $crawler->filter('a');
        foreach ($links as $link) {
            $this->checkLink($link, $uri, $scrapeUrl->getScrape());
        }
    }

    /**
     * @param DOMElement $link
     * @param Uri $uri
     * @param Scrape $scrape
     */
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
