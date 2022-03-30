<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function creationSortie(Request $request, EntityManagerInterface $em, SortieRepository $sortieRepo, EtatRepository $etatRepo): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        //On hydrate l'id organisateur avec l'id de l'utilisateur connecté
        $idOrganisateur = $this->getUser();
        $sortie->setOrganisateur($idOrganisateur);

        //on hydrate l'id site avec l'id du site
        $idSite = $this->getUser()->getSite();
        $sortie->setSite($idSite);

        //Attribuer un état en fonction du bouton cliqué => Etat : Créée
        if (($sortieForm->getClickedButton() === $sortieForm->get('Enregistrer')) && ($sortieForm->isValid() && $sortieForm->isSubmitted())) {
            $etat = $etatRepo->find(1);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "Nouvelle sortie créée");
        }

        //Attribuer un état en fonction du bouton cliqué => Etat : Ouvert
        if ($sortieForm->getClickedButton() === $sortieForm->get('Publier')) {
            $etat = $etatRepo->find(2);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "Sortie publiée");
        }


        return $this->render('sortie/creationSortie.html.twig', ["sortieForm" => $sortieForm->createView()]);
    }

    /**
     * @Route("/getVilles", name="fetch_villes")
     */
    public function getVilles(VilleRepository $villeRepo, LieuRepository $lieuRepo): Response
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $villes = $villeRepo->findAll();
        $jsonVilles = array();
        $lieux = $lieuRepo->findAll();
        $jsonLieux = array();

        for ($i = 0; $i <= sizeof($villes) - 1; $i++) {
            $jsonVilles[$i]["nomVille"] = $villes[$i]->getNomVille();
            $jsonVilles[$i]["codePostal"] = $villes[$i]->getCodePostal();
            $jsonVilles[$i]["id"] = $villes[$i]->getId();
            $idVille = $villes[$i]->getId();

            $k = 0;
                for($j = 0; $j <= sizeof($lieux) -1; $j++){
                    $lieu_ville = $lieux[$j]->getVille();

                    if($idVille == $lieu_ville->getId()){

                        $jsonVilles[$i]["lieux"][$k]["id"] = $lieux[$j]->getId();
                        $jsonVilles[$i]["lieux"][$k]["nom"] = $lieux[$j]->getNomLieu();
                        $jsonVilles[$i]["lieux"][$k]["rue"] = $lieux[$j]->getRue();
                        $jsonVilles[$i]["lieux"][$k]["latitude"] = $lieux[$j]->getLatitude();
                        $jsonVilles[$i]["lieux"][$k]["longitude"] = $lieux[$j]->getLongitude();
                        $jsonVilles[$i]["lieux"][$k]["villeId"] = $villes[$i]->getId();
                        $k++;
                    }
                }
        }
        $jsonContent = json_encode($jsonVilles);
        return $this->json($jsonContent);
    }
}
