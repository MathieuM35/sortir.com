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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

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
        $sortie->addParticipant($this->getUser());

        $villes = $em->getRepository(Ville::class)->findAll();

        $formSortie = $this->createForm(SortieType::class, $sortie, array('villes' => $villes));
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            $erreurs = "";
            //on vérifie les dates
            if ($sortie->getDateHeureDebut() < new \DateTime()) {
                $erreurs .= " La date de debut de la sortie ne peut pas être déjà passée. ";
            }
            if ($sortie->getDateLimiteInscription() < new  \DateTime()) {
                $erreurs .= " La date de debut de limite d'inscription ne peut pas être déjà passée. ";
            }
            if ($sortie->getDateLimiteInscription() > $sortie->getDateHeureDebut()) {
                $erreurs .= " La date limite d'inscription doit être avant le début de la sortie. ";
            }
            if ($sortie->getNbInscriptionsMax() < 2) {
                $erreurs .= " Nombre minimum de participants : 2 ";
            }
            //on set un état différent en fonction du bouton submit cliqué (Enregistrer ou Publier)
            //si clic sur le bouton Enregister on set l'état 'En création'
            if ($formSortie->get('enregister')->isClicked()) {
                $sortie->setEtat($etatRepo->find(1));
            }
            //si clic sur le bouton Publier on set l'état 'Ouverte'
            if ($formSortie->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepo->find(2));
            }

            //on vérifie qu'il n'y a pas eu d'erreurs
            if($erreurs == ""){
                //insertion en BDD
                $em->persist($sortie);
                $em->flush();
                $this->addFlash("success", "Votre sortie a bien été ajoutée !");
                return $this->redirectToRoute("liste_sorties");
            } else {
                $this->addFlash("danger", $erreurs);
                return (['formSortie' => $formSortie->createView()]);
            }

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

        //on vérifie que la date de cloture des inscriptions n'est pas dépassée et que le statut de la sortie soit "Ouverte"
        if ($sortie->getDateLimiteInscription() > new \DateTime() && $sortie->getEtat()->getId() == 2) {

            //si il reste des places, on ajoute l'utilisateur courant au tableau de participants à la sortie
            if (sizeof($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()) {
                $sortie->addParticipant($this->getUser());
                $em->flush();
                $this->addFlash("success", "Vous êtes inscrit à la sortie " . $sortie->getNom() . " !");
            } else {
                $this->addFlash("danger", "Il n'y a plus de place pour la sortie " . $sortie->getNom());
            }
        } else {
            $this->addFlash("danger", "Impossible de s'inscrire à cette sortie");
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

        //on vérifie que la sortie n'a pas commencé
        if ($sortie->getDateHeureDebut() > new \DateTime()) {
            $sortie->removeParticipant($this->getUser());
            $em->flush();
            $this->addFlash("success", "Vous êtes désinscrit à la sortie " . $sortie->getNom());

            return $this->redirectToRoute("liste_sorties");
        } else {
            $this->addFlash("danger", "Vous ne pas vous désister sur une sortie qui a commencée");
        }


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
    function annulation($id, EntityManagerInterface $em, Request $request)
    {
        //récupération de la sortie concernée en fonction de l'id en paramètre
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //on vérifie que l'utilisateur courant est bien l'organisateur de la sortie ou un utilisateur ADMIN
        $user = $this->getUser();
        if ($user == $sortie->getOrganisateur() or $user->getAdministrateur()) {

            //on vérifie que la sortie n'a pas encore commencé
            if ($sortie->getDateHeureDebut() > new \DateTime()) {

                $formAnnulation = $this->createForm(AnnulationType::class);
                $formAnnulation->handleRequest($request);

                if ($formAnnulation->isSubmitted()) {
                    //on récupère le motif d'annulation
                    $formAnnulationData = $formAnnulation->getData();
                    if ($user == $sortie->getOrganisateur()) {
                        $motifAnnulation = "Cette sortie a été annulée par son organisateur. Motif : " . $formAnnulationData['motif'];
                    } else {
                        $motifAnnulation = "Cette sortie a été annulée par un ADMINISTRATEUR. Motif : " . $formAnnulationData['motif'];
                    }

                    //on set l'état "annulée" (etat.id =6) à la sortie
                    $etatRepo = $this->getDoctrine()->getRepository(Etat::class);
                    $sortie->setEtat($etatRepo->find(6));
                    $sortie->setMotifAnnulation($motifAnnulation);
                    $em->persist($sortie);
                    $em->flush();

                    $this->addFlash("success", "La sortie a bien été annulée");
                    $this->redirectToRoute("sortie_details", ['id' => $sortie->getId()]);
                }
            } else {
                $this->add("danger", "Impossible d'annuler la sortie en cours");
            }

            return $this->render("sortie/annulation.html.twig", [
                'formAnnulation' => $formAnnulation->createView(),
                'idSortie' => $sortie->getId(),
                'sortie' => $sortie,
            ]);
        }
    }


}
