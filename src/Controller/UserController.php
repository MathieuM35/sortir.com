<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\ImportCsvType;
use App\Form\MdpReinitialiseType;
use App\Form\MotDePasseType;
use App\Form\ProfilType;
use App\Form\RechercheUtilisateurType;
use App\Form\RechercheVilleType;
use App\Form\RegisterType;
use App\Form\MdpOublieType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;

class UserController extends Controller
{

    /**
     * @Route("/register", name="user-register")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {

        $user = new User();
        $user->setPassword("1234");
        $user->setActif(true);
        $registerForm = $this->createForm(RegisterType::class, $user);
        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "utilisateur enregistré !");
            return $this->redirectToRoute("liste_users");
        }

        return $this->render("user/register.html.twig", ['registerForm' => $registerForm->createView()]);

    }

    /**
     * @Route("/importCSV",name="user-addCSV")
     * @Template()
     */
    public function addCSV(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        //on récupère le fichier
        $importCsvForm = $this->createForm(ImportCsvType::class);
        $importCsvForm->handleRequest($request);

        if ($importCsvForm->isSubmitted() && $importCsvForm->isValid()) {

            if ($importCsvForm['csvfile']->getData() != null) {
                dump($importCsvForm['csvfile']);
                $file = $importCsvForm['csvfile']->getData();
                $fileName = $this->generateUniqueFileName() . '.csv';

                //on stocke le fichier pour ensuite le traiter
                $file->move($this->getParameter('photos_directory'), $fileName);

                //on traite le fichier
                $utilisateurs = array(); // $utilisateurs va contenir les users extraits du fichier csv
                $ligne = 0;

                // Import du fichier CSV
                //$handle = fichier à manipuler
                //$data = ligne du fichier à traiter
                if (($handle = fopen("./uploads/photos/" . $fileName, "r")) !== FALSE) { // Lecture du fichier, à adapter
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Eléments séparés par un point-virgule, à modifier si necessaire
                        $num = count($data); // Nombre d'éléments sur la ligne traitée
                        $ligne++;
                        for ($c = 0; $c < $num; $c++) {
                            $utilisateurs[$ligne] = array(
                                "nom" => $data[0],
                                "prenom" => $data[1],
                                "email" => $data[2],
                                "telephone" => $data[3],
                                "idSite" => $data[4],
                            );
                        }
                    }
                    fclose($handle);
                }

                $siteRepo = $em->getRepository(Site::class);

                //on ajoute en bdd les utilisateurs du tableau $utilisateurs
                foreach ($utilisateurs as $utilisateur) {
                    $user = new User();

                    $hashed = $encoder->encodePassword($user, "1234");
                    $user->setPassword($hashed);
                    $user->setActif(true);
                    $user->setAdministrateur(false);

                    $user->setUsername($utilisateur['prenom'].".".$utilisateur['nom']);
                    $user->setNom($utilisateur['nom']);
                    $user->setPrenom($utilisateur['prenom']);
                    $user->setEmail($utilisateur['email']);
                    $user->setTelephone($utilisateur['telephone']);
                    $user->setSite($siteRepo->find($utilisateur['idSite']));

                    $em->persist($user);
                }
                $em->flush();
                $this->addFlash("success", "Utilisateurs créés avec succès !");
                return $this->redirectToRoute('liste_users');

            } else {
                $this->addFlash("danger", "Aucun fichier soumis");
            }
        }
        return (['importCsvForm' => $importCsvForm->createView()]);
    }


    /**
     * @Route("/user/update", name="user-update")
     */
    public
    function updateProfil(Request $request, EntityManagerInterface $em)
    {
        //Récupération de l'utilisateur connecté
        $user = $this->getUser();
        $photoInitiale = $this->getUser()->getPhoto();
        //Récupération du formulaire de l'utilisateur mis à jour
        $profilForm = $this->createForm(ProfilType::class, $user);
        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            //dump($profilForm['photo']->getData());
            if ($profilForm['photo']->getData() != null) {
                //$file stock le fichier uploadé
                /**
                 * @var Symfony\Component\HttpFoundation\File\UploadedFile $file
                 */
                $file = $user->getPhoto();

                //$fileName = $this->md5(uniqid()).'.'.$file->guessExtension();
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                //Déplacer le fichier dans le dossier où les photos sont stockées
                $file->move(
                    $this->getParameter('photos_directory'),
                    $fileName
                );

                // mettre à jour la propriété 'photo' pour stocker le nom du fichier au lieu de son contenu
                $user->setPhoto($fileName);
            } else {
                $user->setPhoto($photoInitiale);
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "utilisateur enregistré !");
            return $this->redirectToRoute("liste_sorties");
        }

        return $this->render("user/update.html.twig", ['profilForm' => $profilForm->createView()]);

    }

    /**
     * @return string
     */
    public
    function generateUniqueFileName()
    {

        return md5(uniqid());
    }

    /**
     * @Route("/mdp/update", name="mdp-update")
     */
    public
    function updateMotDePasse(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        //Récupération en BDD du mot de passe actuel de l'utilisateur connecté
        $user = $this->getUser();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $utilisateurBDD = $userRepo->find($user->getId());
        $mdpBDD = $utilisateurBDD->getPassword();
        dump('Le mot de passe utilisateur en BDD est : ' . $mdpBDD);
        //Récupération du formulaire de mise à jour du mdp de l'utilisateur

        $mdpForm = $this->createForm(MotDePasseType::class, $user);
        $mdpForm->handleRequest($request);
        $mdpSaisi = $mdpForm['passwordActuel']->getData();

        dump('Le mot de passe saisi est : ' . $mdpSaisi);

        $mdpSaisiCrypte = $encoder->encodePassword($user, $mdpSaisi);
        dump('Le mot de passe saisi crypté est : ' . $mdpSaisiCrypte);

        if (password_verify($mdpSaisi, $mdpBDD)) {
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


        return $this->render("user/mdpupdate.html.twig", ['mdpForm' => $mdpForm->createView()]);
    }

    /**
     * @Route("/login", name="login")
     */
    public
    function login(AuthenticationUtils $authUtils)
    {

        $erreur = $authUtils->getLastAuthenticationError();
        $dernierPseudo = $authUtils->getLastUsername();
        return $this->render("user/login.html.twig", ['dernier_pseudo' => $dernierPseudo, 'erreur' => $erreur]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public
    function logout()
    {

    }

    /**
     * @Route("/profil/{id}", name="detail-profil", requirements={"id" = "\d+"})
     */
    public
    function consulterProfil($id, EntityManagerInterface $em)
    {

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        //si aucun utilisateur n'est trouvé, on lève une exception
        if (empty($user)) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas !");
        }

        return $this->render('user/detail.html.twig', ['user' => $user]);

    }

    /**
     * @Route("/users/liste",name="liste_users")
     */
    public
    function liste(Request $request)
    {

        //barre de recherche d'un utilisateur
        $rechercheUserForm = $this->createForm(RechercheUtilisateurType::class);
        $rechercheUserForm->handleRequest($request);

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findAll();


        if ($rechercheUserForm->isSubmitted() && $rechercheUserForm->isValid()) {
            if ($rechercheUserForm->get('rechercher')->isClicked()) {
                $nomUserContient = $rechercheUserForm->getData();
                $users = $userRepo->findByNomContient($nomUserContient["nomContient"]);
            }
            if ($rechercheUserForm->get('voirTout')->isClicked()) {
                $users = $userRepo->findAll();
            }
        }

        return $this->render('user/liste.html.twig', [
            'rechercheUserForm' => $rechercheUserForm->createView(),
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/liste/desactiver/{id}", name="desactiver_user")
     * @param $id id de l'user a rendre inactif
     * @param EntityManagerInterface $em
     */
    public
    function rendreInactif($id, EntityManagerInterface $em)
    {
        //on récupère le user concerné via l'id passé en paramètre
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        $user->setActif(0);
        $em->persist($user);
        $em->flush();
        $this->addFlash("success", "Utilisateur désactivé avec succès !");
        return $this->redirectToRoute("liste_users");
    }

    /**
     * @Route("/users/liste/activer/{id}", name="activer_user")
     * @param $id id de l'user a rendre actif
     * @param EntityManagerInterface $em
     */
    public
    function rendreActif($id, EntityManagerInterface $em)
    {
        //on récupère le user concerné via l'id passé en paramètre
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        $user->setActif(1);
        $em->persist($user);
        $em->flush();
        $this->addFlash("success", "Utilisateur activé avec succès !");
        return $this->redirectToRoute("liste_users");
    }

    /**
     * @Route("/users/liste/delete/{id}", name="delete_user")
     * @param $id id de l'user a supprimer
     * @param EntityManagerInterface $em
     */
    public
    function delete($id, EntityManagerInterface $em)
    {
        //on récupère le user concerné via l'id passé en paramètre
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        $em->remove($user);
        $em->flush();
        $this->addFlash("success", "Utilisateur supprimé avec succès !");
        return $this->redirectToRoute("liste_users");
    }

    /**
     * @Route("/user/mdp-oublie", name="mdp_oublie"))
     */
    public function recevoirLienMDPOublie(Request $request, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, \Swift_Mailer $mailer){

        $mdpOublieForm = $this->createForm(MdpOublieType::class);
        $mdpOublieForm->handleRequest($request);

        if ($mdpOublieForm->isSubmitted() && $mdpOublieForm->isValid()){

            $email = $mdpOublieForm['email']->getData();
            $userRepo = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepo->findOneByEmail($email);

            if ($user === null){
                $this->addFlash('error', 'Email Inconnu, veuillez saisir votre email !');
                return $this->redirectToRoute('mdp_oublie');
            }

            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('mdp_oublie');
            }

            $urlcible = $this->generateUrl('reinitialiser_mdp', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Oubli de mot de passe - Réinitialisation'))
                ->setFrom(array('admin@sortir.com' => 'Administrateur site'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'user/resetPasswordMail.html.twig',
                        [
                            'user' => $user,
                            'url' => $urlcible
                        ]
                    ),
                    'text/html'
                );
            $mailer->send($message);

            $this->addFlash('success', 'Lien généré et envoyé par mail : veuillez le suivre pour réinitialiser votre mot de passe !');

            //return $this->redirectToRoute('security_login');

            return $this->render('user/lienReinitialisationMdp.html.twig', ['urlCible' => $urlcible, 'token'=>$token]);
        }

        return $this->render('user/mdpoublie.html.twig', ['mdpOublieForm' => $mdpOublieForm->createView()]);
    }

    /**
     * @Route("/user/reinitialiser-mdp/{token}", name="reinitialiser_mdp", requirements={"token" = ".+"})
     */
    public function reinitialiserMdp($token, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder){

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findOneByToken($token);

        $mdpReinitialiseForm = $this->createForm(MdpReinitialiseType::class, $user);
        $mdpReinitialiseForm->handleRequest($request);

        if ($mdpReinitialiseForm->isSubmitted() && $mdpReinitialiseForm->isValid()){
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "nouveau mot de passe enregistré !");
            return $this->redirectToRoute("liste_sorties");

        }

        return $this->render('user/reinitialiserMdp.html.twig', ['mdpReinitialiseForm' => $mdpReinitialiseForm->createView()]);
    }

}
