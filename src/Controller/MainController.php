<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_main")
     */
    public function afficherSorties(Request $request,PaginatorInterface $paginator, SortieRepository $sortieRepo, ParticipantRepository $participantsRepo, SiteRepository $siteRepo): Response
    {
        if($this->isGranted('ROLE_USER') == false){
            return $this->redirectToRoute("app_login");
        }

        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }

        $sites = $siteRepo->findAll();
        $sorties = $sortieRepo->findAll();
        $participants = $participantsRepo->findAll();
        $userId = $this->getUser()->getId();

        if(isset($_POST['submit'])){
            $user = $this->getUser();
            $recherche = $_POST['recherche'];
            $site = $_POST['site'];
            $dateDebut  = $_POST['dateDebut'];
            $dateFin = $_POST['dateFin'];
            $sortiePassee = false;

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

            $sortiePassee = false;
            if(isset($_POST['sortiePassee'])){
                $sortiePassee = true;
            }
           $sorties = $sortieRepo->selectByFilters($user, $recherche, $site, $dateDebut, $dateFin, $jeSuisOrganisateur, $jeSuisInscrit, $jeSuisPasInscrit, $sortiePassee);

        }
        $sorties = $paginator->paginate(
            $sorties,
            $request->query->getInt('page', 1),
            10
        );
        $sorties->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v3_pagination.html.twig');




        return $this->render('main/index.html.twig', compact("sorties", "participants","userId","sites"));
    }
}
