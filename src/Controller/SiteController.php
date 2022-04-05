<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
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

        if($this->isGranted('ROLE_USER') == false){
            return $this->redirectToRoute("app_login");
        }

        $site = new Site();
        $ajoutSiteForm = $this->createForm(SiteType::class, $site);
        $ajoutSiteForm->handleRequest($request);

        if($ajoutSiteForm->isSubmitted() && $ajoutSiteForm->isValid()){
            $nomSite = $site->getNomSite();
            $site->setNomSite($nomSite);

            $this->addFlash("success","Site ajouté");

            $em->persist($site);
            $em->flush();
        }



        return $this->render('site/index.html.twig', ["ajoutSiteForm"=>$ajoutSiteForm->createView()] );
    }

    /**
     * @Route("/listeSites", name="app_listeSites")
     */
    public function listeSites(SiteRepository $siteRepo, Request $request):Response
    {
//        if($this->isGranted('ROLE_USER') == false){
//            return $this->redirectToRoute("app_login");
//        }

        $sites = $siteRepo->findAll();

        if($_POST){
            $recherche = $_POST['rechercher'];
           //dd($recherche);
            $sites = $siteRepo->selectByFilters($recherche);
        }



        return $this->render('site/listeSites.html.twig', compact('sites'));
    }

    /**
     * @Route("/suppSites/{id}", name="app_suppressionSite", requirements={"id"="\d+"})
     */
    public function suppSite(SiteRepository $siteRepo, Request $request, $id , EntityManagerInterface $em):Response
    {

        $site = $siteRepo->find($id);
        //dd($site);
        $em->remove($site);
        $em ->flush();

        return $this->redirectToRoute('app_listeSites', ['id' => $id , 'site' => $site]);
    }


}
