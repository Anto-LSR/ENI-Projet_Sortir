<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_main")
     */
    public function afficherSorties(SortieRepository $sortieRepo, ParticipantRepository $participantsRepo): Response
    {
        if($this->isGranted('ROLE_USER') == false){
            return $this->redirectToRoute("app_login");
        }

        $sorties = $sortieRepo->findAll();
        $participants = $participantsRepo->findAll();
        $userId = $this->getUser()->getId();



        return $this->render('main/index.html.twig', compact("sorties", "participants","userId"));
    }
}
