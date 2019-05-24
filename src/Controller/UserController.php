<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MotDePasseType;
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
     * @Route("/mdp/update", name="mdp-update")
     */
    public function updateMotDePasse(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder){
        //Récupération en BDD du mot de passe actuel de l'utilisateur connecté
        $user = $this->getUser();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $utilisateurBDD = $userRepo->find($user->getId());
        $mdpBDD = $utilisateurBDD->getPassword();
        dump('Le mot de passe utilisateur en BDD est : '.$mdpBDD);
        //Récupération du formulaire de mise à jour du mdp de l'utilisateur
        $mdpForm = $this->createForm(MotDePasseType::class, $user, ['method' => 'GET']);
        $mdpForm->handleRequest($request);
        $mdpSaisi = $mdpForm['passwordActuel']->getData();

        dump('Le mot de passe saisi est : '.$mdpSaisi);

        $mdpSaisiCrypte = $encoder->encodePassword($user, $mdpSaisi);
        dump('Le mot de passe saisi crypté est : '.$mdpSaisiCrypte);

        if(password_verify($mdpSaisi, $mdpBDD)){
        //if ($mdpSaisiCrypte == $mdpBDD){
            if ($mdpForm->isSubmitted() && $mdpForm->isValid()) {
                $hashed = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($hashed);
                $em->persist($user);
                $em->flush();
                $this->addFlash("success", "nouveau mot de passe enregistré !");
                $this->redirectToRoute("liste_sorties");
            }
        } else {
            $this->addFlash("error", "Ancien mot de passe incorrect !");
        }


        return $this->render("user/mdpupdate.html.twig", ['mdpForm'=>$mdpForm->createView()]);
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

    /**
     * @Route("/profil/{id}", name="detail-profil", requirements={"id" = "\d+"})
     */
    public function consulterProfil($id, EntityManagerInterface $em){

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        //si aucun utilisateur n'est trouvé, on lève une exception
        if (empty($user)){
            throw $this->createNotFoundException("Cet utilisateur n'existe pas !");
        }

        return $this->render('user/detail.html.twig', ['user' => $user]);

    }

}
