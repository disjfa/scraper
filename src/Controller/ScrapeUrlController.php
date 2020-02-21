<?php

namespace App\Controller;

use App\Entity\ScrapeUrl;
use App\Service\ScrapeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @Route("/scrape/url")
 */
class ScrapeUrlController extends AbstractController
{
    /**
     * @Route("/{scrapeUrl}/show", name="scrape_url_show")
     * @param ScrapeUrl $scrapeUrl
     * @return Response
     */
    public function show(ScrapeUrl $scrapeUrl)
    {
        return $this->render('scrape_url/show.html.twig', [
            'scrapeUrl' => $scrapeUrl,
        ]);
    }

    /**
     * @Route("/{scrapeUrl}/scrape", name="scrape_url_scrape")
     * @param ScrapeUrl $scrapeUrl
     * @param ScrapeService $scrapeService
     * @return RedirectResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrape(ScrapeUrl $scrapeUrl, ScrapeService $scrapeService)
    {
        $scrapeService->scrape($scrapeUrl);

        $this->addFlash('success', 'Reindexed scrape');

        return $this->redirectToRoute('scrape_url_show', [
            'scrapeUrl' => $scrapeUrl->getId(),
        ]);
    }
}
