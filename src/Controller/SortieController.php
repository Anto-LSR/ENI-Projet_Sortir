<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AjoutLieuType;
use App\Form\CreationSortieType;
use App\Form\ModifSortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
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

    private $sortieRepo;

    function __construct(SortieRepository $sortieRepo)
    {
        $this->sortieRepo = $sortieRepo;
    }

    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function creationSortie(Request $request,VilleRepository $villeRepo, LieuRepository $lieuRepo,EntityManagerInterface $em, SortieRepository $sortieRepo, EtatRepository $etatRepo): Response
    {
        $lieu = new Lieu();
        $ajoutLieuForm = $this->createForm(AjoutLieuType::class, $lieu);
        $ajoutLieuForm->handleRequest($request);

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
            //dd($_POST);
            $ville = $villeRepo->find($_POST["ville"]);
            $lieu = $lieuRepo->find($_POST["lieu"]);
            $lieu->setVille($ville);
            $sortie->setLieu($lieu);
            $etat = $etatRepo->find(1);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "Nouvelle sortie créée");
        }

        //Attribuer un état en fonction du bouton cliqué => Etat : Ouvert
        if ($sortieForm->getClickedButton() === $sortieForm->get('Publier') && ($sortieForm->isValid() && $sortieForm->isSubmitted())) {
            $ville = $villeRepo->find($_POST["ville"]);
            $lieu = $lieuRepo->find($_POST["lieu"]);
            $lieu->setVille($ville);
            $sortie->setLieu($lieu);
            $etat = $etatRepo->find(2);
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "Sortie publiée");
        }



        return $this->render('sortie/creationSortie.html.twig', ["sortieForm" => $sortieForm->createView(), "lieuForm" => $ajoutLieuForm->createView()]);
    }

    /**
     * @Route("/sortie/{id}", name="app_detailSortie", requirements={"id"="\d+"})
     */
    public function afficherDetailSortie($id, SiteRepository $siteRepo, LieuRepository $lieuRepo, VilleRepository $villeRepo): Response
    {
        $sortie = $this->sortieRepo->find($id);
        $sites = $siteRepo->findAll();
        $lieux = $lieuRepo->findAll();
        $villes = $villeRepo->findAll();
        //dd($sites);
        //dd($sortie);
        //dd($lieux);
        return $this->render('sortie/detailSortie.html.twig', compact("sortie", "sites", "lieux", "villes"));
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
            for ($j = 0; $j <= sizeof($lieux) - 1; $j++) {
                $lieu_ville = $lieux[$j]->getVille();

                if ($idVille == $lieu_ville->getId()) {

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

    /**
     * @Route("/addLieu", name="add_lieu")
     */
    public function addLieu(Request $req, LieuRepository $lieuRepo, VilleRepository $villeRepo)
    {
        $data = json_decode($req->getContent());
        $lieu = new Lieu();
        $ville = $villeRepo->find($data->ville);
        $lieu->setNomLieu($data->nomLieu);
        $lieu->setRue($data->rue);
        $lieu->setLatitude($data->latitude);
        $lieu->setLongitude($data->longitude);
        $lieu->setVille($ville);

        $lieuRepo->add($lieu);
        return $this->json($req->getContent());
    }

    /**
     * @Route("/modifSortie/{id}", name="app_modifSortie", requirements={"id"="\d+"})
     */
    public function modifierSortie(Request $request, $id, EntityManagerInterface $em, SiteRepository $siteRepo, EtatRepository $etatRepo, LieuRepository $lieuRepo, VilleRepository $villeRepo): Response
    {
        $sortie = new Sortie();
        $lieu = new Lieu();

        $sortie = $this->sortieRepo->find($id);
        $sites = $siteRepo->findAll();
        $lieux = $lieuRepo->findAll();
        $villes = $villeRepo->findAll();


        $lieuForm = $this->createForm(AjoutLieuType::class, $lieu);
        $modifSortieForm = $this->createForm(ModifSortieType::class, $sortie);

        $lieuForm->handleRequest($request);
        $modifSortieForm->handleRequest($request);

        //Attribuer un état en fonction du bouton cliqué
        if (($modifSortieForm->isSubmitted() && $modifSortieForm->isValid())) {
            $etat = $etatRepo->find(1);
            //dd($sortie->getOrganisateur());
            if($modifSortieForm->getClickedButton() === $modifSortieForm->get('Enregistrer')){
                $etat = $etatRepo->find(1);
                $sortie->setEtat($etat);
                $this->addFlash("success", "Sortie modifiée avec succès");
            } else if ($modifSortieForm->getClickedButton() === $modifSortieForm->get('Publier')){
                $etat = $etatRepo->find(2);
                $sortie->setEtat($etat);
                $this->addFlash("success", "Sortie publiée");
            }
            $ville = $villeRepo->find($_POST["ville"]);
            $lieu = $lieuRepo->find($_POST["lieu"]);
            $lieu->setVille($ville);
            $sortie->setLieu($lieu);
            $em->flush();

        }



        //Supprime de la BDD ------------------- TODOTODOTODTODOTODOTODOTODOTODO
        if ($modifSortieForm->getClickedButton() === $modifSortieForm->get('Supprimer') && $modifSortieForm->isValid() && $modifSortieForm->isSubmitted()){
            $etat = $etatRepo->find(6);
            $sortie->setEtat($etat);

            $em->flush();
            $this->addFlash("success", "Sortie publiée");
    }



        return $this->render('sortie/modifierSortie.html.twig', ["modifSortieForm"=> $modifSortieForm->createView(), "lieuForm"=>$lieuForm->createView(), "sortie"=>$sortie]);
    }

}