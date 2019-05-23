<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends Controller
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/register", name="user-register")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder){

        $user = new User();
        $user->setPassword("1234");
        $user->setActif(true);
        $registerForm = $this->createForm(RegisterType::class, $user);
        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()){
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "utilisateur enregistré !");
            $this->redirectToRoute("liste_sorties");
        }

        return $this->render("user/register.html.twig", ['registerForm'=>$registerForm->createView()]);

    }

    /**
     * @Route("/user/update", name="user-update")
     */
    public function updateProfil(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder){
        //Récupération de l'utilisateur connecté
        $user = $this->getUser();
        //Récupération du formulaire de l'utilisateur mis à jour
        $profilForm = $this->createForm(ProfilType::class, $user);
        $profilForm->handleRequest($request);
        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "utilisateur enregistré !");
            $this->redirectToRoute("liste_sorties");
        }

        return $this->render("user/update.html.twig", ['profilForm'=>$profilForm->createView()]);

    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authUtils){

        $erreur = $authUtils->getLastAuthenticationError();
        $dernierPseudo = $authUtils->getLastUsername();
        return $this->render("user/login.html.twig", ['dernier_pseudo' => $dernierPseudo, 'erreur' => $erreur]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){

    }

}
