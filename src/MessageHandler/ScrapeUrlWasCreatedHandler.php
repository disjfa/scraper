<?php

namespace App\MessageHandler;

use App\Entity\ScrapeUrl;
use App\Message\ScrapeUrlWasCreated;
use App\Repository\ScrapeUrlRepository;
use App\Service\ScrapeService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ScrapeUrlWasCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var ScrapeService
     */
    private $scrapeService;
    /**
     * @var ScrapeUrlRepository
     */
    private $scrapeUrlRepository;

    /**
     * ScrapeUrlWasCreatedHandler constructor.
     * @param ScrapeService $scrapeService
     * @param ScrapeUrlRepository $scrapeUrlRepository
     */
    public function __construct(ScrapeService $scrapeService, ScrapeUrlRepository $scrapeUrlRepository)
    {
        $this->scrapeService = $scrapeService;
        $this->scrapeUrlRepository = $scrapeUrlRepository;
    }

    /**
     * @param ScrapeUrlWasCreated $scrapeUrlWasCreated
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __invoke(ScrapeUrlWasCreated $scrapeUrlWasCreated)
    {
        $scrapeUrl = $this->scrapeUrlRepository->find($scrapeUrlWasCreated->getScrapeId());

        if (false === $scrapeUrl instanceof ScrapeUrl) {
            return;
        }

        $this->scrapeService->scrape($scrapeUrl);
    }
}
