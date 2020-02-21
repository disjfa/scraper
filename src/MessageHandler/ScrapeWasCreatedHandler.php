<?php

namespace App\MessageHandler;

use App\Entity\Scrape;
use App\Entity\ScrapeUrl;
use App\Message\ScrapeWasCreated;
use App\Service\ProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ScrapeWasCreatedHandler implements MessageHandlerInterface
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

    public function __construct(EntityManagerInterface $entityManager, ProcessorService $processorService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->processorService = $processorService;
        $this->logger = $logger;
    }

    public function __invoke(ScrapeWasCreated $scrapeWasCreated)
    {
        /** @var Scrape $scrape */
        $scrape = $this->entityManager->getRepository(Scrape::class)->find($scrapeWasCreated->getScrapeId());

        if (false === $scrape instanceof Scrape) {
            return;
        }

        $scrapeUrl = new ScrapeUrl($scrape, $scrape->getUrl());

        $this->logger->info('Start logging: ' . $scrape->getUrl());

        $this->entityManager->persist($scrapeUrl);
        $this->entityManager->flush();

        $this->processorService->handle($scrapeUrl);
    }
}
