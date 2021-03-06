<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\CSVRegisterType;
use App\Form\ModifProfilType;
use App\Form\UpdatePasswordType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
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
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        //On récupère l'utilisateur connecté
        $user = $this->getUser();
        $oldPassword = $user->getPassword();

        $modifProfilForm = $this->createForm(ModifProfilType::class, $user);
        $modifProfilForm->handleRequest($request);

        if ($modifProfilForm->isSubmitted() && $modifProfilForm->isValid()) {
            $password = $_POST["verifPassword"];
            $isCorrect = password_verify($password, $oldPassword);
            if ($modifProfilForm['photo']->getData()) {
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
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
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

    /**
     * @Route("/admin/gestion_utilisateur", name="app_admin_gestion")
     */
    public function userManager(Request $request, ParticipantRepository $partRepo, PaginatorInterface $paginator): Response
    {
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        $users = $partRepo->findAll();
        $users = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            5
        );
        $users->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v3_pagination.html.twig');
        return $this->render('user/gestion_utilisateurs.twig', compact('users'));
    }

    /**
     * @Route("/admin/desactiver/{id}", name="app_disable_user", requirements={"id"="\d+"})
     */
    public function disableUser($id, ParticipantRepository $partRepo, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        $user = $partRepo->find($id);
        $user->setActif(false);
        $user->setRoles(["ROLE_DISABLED"]);
        $em->flush();

        return $this->redirectToRoute('app_admin_gestion');

    }

    /**
     * @Route("/admin/reactiver/{id}", name="app_enable_user", requirements={"id"="\d+"})
     */
    public function enableUser($id, ParticipantRepository $partRepo, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        $user = $partRepo->find($id);
        $user->setActif(true);
        $user->setRoles(["ROLE_USER"]);
        $em->flush();

        return $this->redirectToRoute('app_admin_gestion');

    }

    /**
     * @Route("/admin/supprimer/{id}", name="app_remove_user", requirements={"id"="\d+"})
     */
    public function removeUser($id, ParticipantRepository $partRepo, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        $user = $partRepo->find($id);
        $partRepo->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_gestion');

    }

    /**
     * @Route("/compte-desactive", name="app_disabled")
     */

    public function showDisabled():Response{
        return $this->render('user/compte_desactive.html.twig');
    }

//    /**
//     * @Route("/inscription-csv", name="app_register_csv")
//     */
//    public function csvForm(Request $request, SluggerInterface $slugger): Response
//    {
//        if ($_POST) {
//            dd($request);
//            dd(gettype($csv));
//            dd(($csv->getClientOriginalName()));
//            $originalFileName = pathinfo(($csv->getClientOriginalName()));
//            $safeFileName = $slugger->slug($originalFileName["filename"]);
//            $newFileName = $safeFileName . '-' . uniqid() . '.' . 'csv';
//            try {
//                $csv->move($this->getParameter('csv'), $newFileName);
//                $path = '/csv/' . $newFileName;
//                $stream = fopen($csv);
//                $handle = @fopen($path, "r");
//                $csvToArray = fgetcsv($handle);
//                dd($csvToArray);
//
//            } catch (FileException $e) {
//
//            }
//        }
//
//
//        return $this->render('registration/inscription_csv.html.twig');
//    }


}
