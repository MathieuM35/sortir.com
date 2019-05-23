<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AnnulationType;
use App\Form\RechercheSortieType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SortieController extends Controller
{

    /**
     * Afficher sur la page d'accueil l'ensemble de toutes les sorties présentes en BDD
     * @Route("/",name="liste_sorties")
     * @Template()
     */
    public function listeToutesSorties(Request $request)
    {
        //par defaut on récupère toutes les sorties
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();

        //on récupère le current user
        $user = $this->getUser();

        //formulaire de recherche de sorties
        $rechercheForm = $this->createForm(RechercheSortieType::class);
        $rechercheForm->handleRequest($request);

        //on traite les critères de recherche
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()) {
            //$criteres contient les réponses au formulaire
            $criteres = $rechercheForm->getData();
            //on fait appel à une methode perso dans le repository
            $sorties = $sortieRepo->findSortiesSelonRecherche($criteres, $user);
            $this->addFlash("success", "Il y a " . sizeof($sorties) . " sortie(s) correspondant à votre recherche !");
        } else {
            //si aucun critère renseigné, on affiche toutes les sorties de la BDD
            $sorties = $sortieRepo->findAll();
        }

        return (['rechercheForm' => $rechercheForm->createView(),
            'sorties' => $sorties]);


    }

    /**
     * @param $id id la sortie à afficher
     * @Route("/sortie/{id}", name="sortie_details")
     */
    public function details($id)
    {
        //on récupère la sortie selon l'id en paramèrte
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //si aucune sortie n'est trouvée avec cet idée, on lève une exception
        if (empty($sortie)) {
            throw  $this->createNotFoundException("Cette sortie n'existe pas");
        }

        return $this->render('sortie/details.html.twig', ['sortie' => $sortie]);
    }

    /**
     * Page Créer une sortie
     * @Route("/creer-sortie",name="creer_sortie")
     * @Template()
     */
    public function add(Request $request,
                        EntityManagerInterface $em)
    {
        $sortie = new Sortie();
        $etatRepo = $this->getDoctrine()->getRepository(Etat::class);
        $sortie->setOrganisateur($this->getUser());
        $sortie->setParticipants(array());

        $villes = $em->getRepository(Ville::class)->findAll();

        $formSortie = $this->createForm(SortieType::class, $sortie, array('villes' => $villes));
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            //on set un état différent en fonction du bouton submit cliqué (Enregistrer ou Publier)
            //si clic sur le bouton Enregister on set l'état 'En création'
            if ($formSortie->get('enregister')->isClicked()) {
                $sortie->setEtat($etatRepo->find(1));
            }
            //si clic sur le bouton Publier on set l'état 'Ouverte'
            if ($formSortie->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepo->find(2));
            }
            //insertion en BDD
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Votre sortie a bien été ajoutée !");
            return $this->redirectToRoute("liste_sorties");
        } else {
            return (['formSortie' => $formSortie->createView()]);
        }
    }

    /**
     * S'inscire à une sortie
     * @Route("/sortie/inscription/{id}", requirements={"id" = "\d+"}, name="inscription")
     * @param $id id de la sortie
     *
     */
    public function inscription($id, EntityManagerInterface $em)
    {
        //on récupère la sortie selon l'id en paramèrte
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //si il reste des places, on ajoute l'utilisateur courant au tableau de participants à la sortie
        if (sizeof($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()) {
            $sortie->addParticipant($this->getUser());
            $em->flush();
            $this->addFlash("success", "Vous êtes inscrit à la sortie " . $sortie->getNom() . " !");
        } else {
            $this->addFlash("danger", "Il n'y a plus de place pour la sortie " . $sortie->getNom());
        }

        return $this->redirectToRoute("liste_sorties");
    }

    /**
     * Se désister pour une sortie
     * @Route("/sortie/desistement/{id}", requirements={"id" = "\d+"}, name="desistement")
     * @param $id id de la sortie
     */
    public function desistement($id, EntityManagerInterface $em)
    {
        //on récupère la sortie selon l'id en paramèrte
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        $sortie->removeParticipant($this->getUser());
        $em->flush();
        $this->addFlash("success", "Vous êtes désinscrit à la sortie " . $sortie->getNom());

        return $this->redirectToRoute("liste_sorties");
    }

    /**
     * Modifier une sortie
     * @Route("/sortie/modification/{id}", requirements={"id" = "\d+"}, name="modification")
     * @param $id
     * @param EntityManagerInterface $em
     */
    public function modification($id, EntityManagerInterface $em, Request $request)
    {
        //récupération de la sortie concernée en fonction de l'id en paramètre
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //on vérifie que l'utilisateur courant est bien l'organisateur de la sortie
        $user = $this->getUser();
        if ($user == $sortie->getOrganisateur()) {
            //Récupération du formulaire de sortie
            $formSortie = $this->createForm(SortieType::class, $sortie);
            $formSortie->handleRequest($request);

            //traitement du form
            if ($formSortie->isSubmitted() && $formSortie->isValid()) {
                $messageFlash = "Sortie mise à jour";
                //si clic sur le bouton Publier on set l'état 'Ouverte'
                if ($formSortie->get('publier')->isClicked()) {
                    $etatRepo = $this->getDoctrine()->getRepository(Etat::class);
                    $sortie->setEtat($etatRepo->find(2));
                    $messageFlash .= " et publiée !";
                }

                $em->persist($sortie);
                $em->flush();
                $this->addFlash("success", $messageFlash);
                $this->redirectToRoute("sortie_details", ['id' => $sortie->getId()]);
            }
            return $this->render("sortie/modification.html.twig", [
                'formSortie' => $formSortie->createView(),
                'idSortie' => $sortie->getId(),
                'sortie' => $sortie,
            ]);
        }
    }

    /**
     * Annuler une sortie
     * @Route("/sortie/annulation/{id}", requirements={"id" = "\d+"}, name="annulation")
     * @param $id
     * @param EntityManagerInterface $em
     */
    public
    function annulation($id, EntityManagerInterface $em)
    {
        //récupération de la sortie concernée en fonction de l'id en paramètre
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //on vérifie que l'utilisateur courant est bien l'organisateur de la sortie
        $user = $this->getUser();
        if ($user == $sortie->getOrganisateur()) {
            $formAnnulation = $this->createForm(AnnulationType::class);

            return $this->render("sortie/annulation.html.twig", [
                'formAnnulation' => $formAnnulation->createView(),
                'idSortie' => $sortie->getId(),
                'sortie' => $sortie,
            ]);
        }
    }

}
