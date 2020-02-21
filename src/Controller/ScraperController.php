<?php

namespace App\Controller;

use App\Entity\Scrape;
use App\Entity\ScrapeUrl;
use App\Form\ScrapeFormType;
use App\Repository\ScrapeRepository;
use App\Service\ProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScraperController extends AbstractController
{
    /**
     * @Route("/scrape", name="app_scrape_index")
     * @param ScrapeRepository $scrapeRepository
     * @return Response
     */
    public function index(ScrapeRepository $scrapeRepository)
    {
        return $this->render('scraper/index.html.twig', [
            'scrapes' => $scrapeRepository->findPaginated(),
        ]);
    }

    /**
     * @Route("/scrape/{scrape}/show", name="app_scrape_show")
     * @param Scrape $scrape
     * @return Response
     */
    public function show(Scrape $scrape)
    {
        return $this->render('scraper/show.html.twig', [
            'scrape' => $scrape,
        ]);
    }

    /**
     * @Route("/scrape/{scrape}/reset", name="app_scrape_reset")
     * @param Scrape $scrape
     * @param EntityManagerInterface $entityManager
     * @param ProcessorService $processorService
     * @return Response
     */
    public function reset(Scrape $scrape, EntityManagerInterface $entityManager, ProcessorService $processorService)
    {
        foreach ($scrape->getUrls() as $scrapeUrl) {
            $entityManager->remove($scrapeUrl);
        }
        $entityManager->flush();

        $scrapeUrl = new ScrapeUrl($scrape, $scrape->getUrl());
        $entityManager->persist($scrapeUrl);
        $entityManager->flush();
        $processorService->handle($scrapeUrl);

        return $this->redirectToRoute('app_scrape_show', [
            'scrape' => $scrape->getId(),
        ]);
    }

    /**
     * @Route("/scrape/create", name="app_scrape_create")
     * @param Request $request
     * @param ProcessorService $processorService
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function create(Request $request, ProcessorService $processorService, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(ScrapeFormType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            /** @var Scrape $scrape */
            $scrape = $form->getData();

            $entityManager->persist($scrape);
            $entityManager->flush();

            $processorService->handle($scrape);

            $this->addFlash('success', 'created');
        }
        return $this->render('scraper/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
