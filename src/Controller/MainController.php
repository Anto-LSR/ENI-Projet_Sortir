<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
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
    public function afficherSorties(SortieRepository $sortieRepo, ParticipantRepository $participantsRepo, SiteRepository $siteRepo): Response
    {
        if($this->isGranted('ROLE_USER') == false){
            return $this->redirectToRoute("app_login");
        }

        $sites = $siteRepo->findAll();
        $sorties = $sortieRepo->findAll();
        $participants = $participantsRepo->findAll();
        $userId = $this->getUser()->getId();

        if(isset($_POST['submit'])){
            $recherche = $_POST['recherche'];
            $site = $_POST['site'];
            $dateDebut  = new \DateTime($_POST['dateDebut']);
            $dateFin = new \DateTime($_POST['dateFin']);

            $jeSuisOrganisateur = false;
            if(isset($_POST['jeSuisOrganisateur'])) {
                $jeSuisOrganisateur = true;
            }

            $jeSuisInscrit = false;
            if(isset($_POST['jeSuisInscrit'])) {
                $jeSuisInscrit = true;
            }

            $jeSuisPasInscrit = false;
            if(isset($_POST['jeSuisPasInscrit'])) {
                $jeSuisPasInscrit = true;
            }
           $sorties = $sortieRepo->selectByFilters($recherche, $site, $dateDebut, $dateFin, $jeSuisOrganisateur, $jeSuisInscrit, $jeSuisPasInscrit);

        }





        return $this->render('main/index.html.twig', compact("sorties", "participants","userId","sites"));
    }
}
