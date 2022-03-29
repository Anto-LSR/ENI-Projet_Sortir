<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_main")
     */
    public function afficherSorties(SortieRepository $sortieRepo, ParticipantRepository $participantsRepo): Response
    {
        $sorties = $sortieRepo->findAll();
        $participants = $participantsRepo->findAll();

        return $this->render('main/index.html.twig', compact("sorties", "participants"));
    }
}
