<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //VERIFICATION ACCES AU REGISTER
        if($this->isGranted('ROLE_DISABLED')){
            return $this->redirectToRoute("app_disabled");
        }
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }else if($this->getUser()->getAdministrateur() == false ){
            return $this->redirectToRoute('app_login');
        }
        //*******************************
        $user = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            if($user->getAdministrateur()){
                $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
            }
            $user->setActif(true);
            //---------------------------------
//            $user->setAdministrateur(false);
//            $user->setActif(true);
            //-------------------------------
            $this->addFlash("success", "Utilisateur enregistrĂ©");
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
