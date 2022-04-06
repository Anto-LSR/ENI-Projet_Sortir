<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use ContainerR7Fm6Jy\getSiteRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    /**
     * @Route("/site", name="app_site")
     */
    public function ajoutSite(Request $request, SiteRepository $siteRepo, EntityManagerInterface $em): Response
    {

        if ($this->isGranted('ROLE_USER') == false) {
            return $this->redirectToRoute("app_login");
        }

        $site = new Site();
        $ajoutSiteForm = $this->createForm(SiteType::class, $site);
        $ajoutSiteForm->handleRequest($request);

        if ($ajoutSiteForm->isSubmitted() && $ajoutSiteForm->isValid()) {

            //verification si le site existe déjà
                //je recupère ce que l'administrateur saisi
                $nomSite = $site->getNomSite();

                //On hydrate l'attribu
                $site->setNomSite($nomSite);
                $nomSite = $site->getNomSite();
                //dd($nomSite);

                $siteName = $siteRepo->findOneBy(['nomSite' => $nomSite]);


                if($siteName == null){
                    $this->addFlash("success", "Site ajouté");
                    $em->persist($site);
                } else{
                    $this->addFlash("danger", "Ce site existe déjà");
                }

                $em->flush();
        }

        return $this->render('site/index.html.twig', ["ajoutSiteForm" => $ajoutSiteForm->createView()]);
    }

    /**
     * @Route("/listeSites", name="app_listeSites")
     */
    public function listeSites(SiteRepository $siteRepo, Request $request): Response
    {

        $sites = $siteRepo->findAll();

        if ($_POST) {
            $recherche = $_POST['rechercher'];
            //dd($recherche);
            $sites = $siteRepo->selectByFilters($recherche);
        }


        return $this->render('site/listeSites.html.twig', compact('sites'));
    }

    /**
     * @Route("/suppSites/{id}", name="app_suppressionSite", requirements={"id"="\d+"})
     */
    public function suppSite(SiteRepository $siteRepo, Request $request, $id, EntityManagerInterface $em): Response
    {

        $site = $siteRepo->find($id);
        $participants = $site->getParticipants();
        $i = 0;
        foreach ($participants as $participant) {
            $i++;
        }
        if($i == 0){
            $em->remove($site);
            $em->flush();
            $this->addFlash("success", "Le site a été supprimé avec succès");

        } else {
            $this->addFlash("danger", "Impossible de supprimer ce site car il comprend des utilisateurs");
        }


        return $this->redirectToRoute('app_listeSites', ['id' => $id, 'site' => $site]);
    }


}
