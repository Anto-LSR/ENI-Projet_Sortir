<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifProfilType;
use App\Form\UpdatePasswordType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    private $partRepo;

    function __construct(ParticipantRepository $partRepo)// injection de dépendances
    {
        $this->partRepo = $partRepo;
    }


    /**
     * @Route("/profil/{id}", name="app_profil", requirements={"id"="\d+"})
     */
    public function afficherProfil($id, SiteRepository $siteRepo): Response
    {
        $peutConsulterProfil = false;
        $visiteur = $this->getUser();
        $sortiesVisiteur = $visiteur->getSorties();
        foreach ($sortiesVisiteur as $sortie) {
            foreach ($sortie->getParticipants() as $profileUser) {
                if ($profileUser->getId() == $visiteur->getId()) {
                    $peutConsulterProfil = true;
                }
            }
        }

        $participant = $this->partRepo->find($id);
        $site = $siteRepo->findAll();


        return $this->render('user/afficherProfil.html.twig', compact("participant", "site", "peutConsulterProfil"));
    }

    /**
     * @Route("/monProfil", name="app_monProfil")
     */
    public function profil(ParticipantRepository $partRepo, SluggerInterface $slugger, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        //On récupère l'utilisateur connecté
        $user = $this->getUser();
        $oldPassword = $user->getPassword();

        $modifProfilForm = $this->createForm(ModifProfilType::class, $user);
        $modifProfilForm->handleRequest($request);

        if ($modifProfilForm->isSubmitted() && $modifProfilForm->isValid()) {
            $password = $_POST["verifPassword"];
            $isCorrect = password_verify($password, $oldPassword);
            $photo = $modifProfilForm['photo']->getData();
            $extension = $photo->guessExtension();
            if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif') {
                $originalFilename = pathinfo($photo->getClientOriginalName());
                $safeFileName = $slugger->slug($originalFilename["filename"]);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $photo->guessExtension();
                try {
                    $photo->move($this->getParameter('uploads'),
                        $newFileName);
                    $path = '/uploads/' . $newFileName;

                    $user = $partRepo->find($this->getUser()->getId());
                    $user->setPhoto($path);

                } catch (FileException $e) {

                }

                //$photo->move($this->getParameter('uploads'), 'profile_picture'.rand(1, 99999).'.'.$extension);
            }

            if ($isCorrect) {
                $this->addFlash("success", "Modifications enregistrées");
                // on insère les nouvelles informations dans la base de données

                $em->flush();


            } else {


                $this->addFlash("danger", "Votre mot de passe est incorrect");
                return $this->redirectToRoute('app_monProfil');


            }
        }


        return $this->render('user/index.html.twig', ["user" => $user, "modifProfilForm" => $modifProfilForm->createView()]);
    }

    /**
     * @Route("/monProfil/changer-motdepasse", name="app_updatePW")
     */
    public function updatePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, ParticipantRepository $pr): Response
    {
        //On récupère l'utilisateur
        $user = $this->getUser();
        //On récupère le mot de passe actuel de l'utilisateur
        $oldPassword = $user->getPassword();

        //on créée le formulaire
        $modifMDPForm = $this->createForm(UpdatePasswordType::class, $user);
        //On vide le champ "Mot de passe" du formulaire (pour ne pas afficher le mdp hashé)
        $modifMDPForm->get('password')->setData('');
        $modifMDPForm->handleRequest($request);


        if ($modifMDPForm->isSubmitted() && $modifMDPForm->isValid()) {

            $oldPassword = $_POST["update_password"]["password"];
            $oldHashedPassword = $userPasswordHasher->hashPassword(
                $user,
                $oldPassword
            );

            if ($oldPassword === $user->getPassword()) {
                $newHashedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $_POST["update_password"]["plainPassword"]["first"]
                );
                $userID = $this->getUser()->getId();
                //dd($user);
                $user->setPassword($newHashedPassword);
                $em->flush();
            }


        }


        return $this->render('user/modifier-mdp.html.twig', ["user" => $user, "modifMDPForm" => $modifMDPForm->createView()]);
    }


}
