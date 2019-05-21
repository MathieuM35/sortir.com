<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @Route("/login", name="login")
     */
    public function login(){

        return $this->render("user/login.html.twig", []);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){

    }

}
