<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function creationSortie(Request $request, EntityManagerInterface $em,SortieRepository $sortieRepo, EtatRepository $etatRepo): Response
    {
        $sortie = new Sortie();

        $sortieForm =$this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        //On hydrate l'id organisateur avec l'id de l'utilisateur connecté
        $idOrganisateur = $this->getUser();
        $sortie->setOrganisateur($idOrganisateur);

        //on hydrate l'id site avec l'id du site
        $idSite = $this->getUser()->getSite();
        $sortie->setSite($idSite);

        //Attribuer un état en fonction du bouton cliqué => Etat : Créée
        if($sortieForm->getClickedButton() === $sortieForm->get('Enregistrer'))
        {
            $etat = $etatRepo->find(1);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
        }

        //Attribuer un état en fonction du bouton cliqué => Etat : Ouvert
        if($sortieForm->getClickedButton() === $sortieForm->get('Publier'))
        {
            $etat = $etatRepo->find(2);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
        }




        return $this->render('sortie/creationSortie.html.twig', ["sortieForm"=>$sortieForm->createView()]);
    }

}
