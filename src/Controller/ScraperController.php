<?php

namespace App\Controller;

use App\Entity\Scrape;
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
