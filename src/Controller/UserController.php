<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifProfilType;
use App\Form\UpdatePasswordType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    function __construct(ParticipantRepository $partRepo)// injection de dépendances
    {
        $this->partRepo = $partRepo;
    }

    /**
     * @Route("/profil", name="app_profil")
     */
    public function profil(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        //On récupère l'utilisateur connecté
        $user = $this->getUser();
        $oldPassword = $user->getPassword();

        $modifProfilForm = $this->createForm(ModifProfilType::class, $user);
        $modifProfilForm->handleRequest($request);

        if ($modifProfilForm->isSubmitted() && $modifProfilForm->isValid()) {
            $password = $_POST["verifPassword"];
            $isCorrect = password_verify($password, $oldPassword);

            if ($isCorrect) {
                    $this->addFlash("success", "Modifications enregistrées");
                    // on insère les nouvelles informations dans la base de données

                    $em->flush();


            } else {


                $this->addFlash("danger", "Votre mot de passe est incorrect");
                return $this->redirectToRoute('app_profil');


            }
        }


        return $this->render('user/index.html.twig', ["user" => $user, "modifProfilForm" => $modifProfilForm->createView()]);
    }

    /**
     * @Route("/profil/changer-motdepasse", name="app_updatePW")
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
