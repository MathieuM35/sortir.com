<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\RechercheSortieType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();

        //formulaire de recherche de sorties
        $rechercheForm = $this->createForm(RechercheSortieType::class);
        $rechercheForm->handleRequest($request);

        //on traite les critères de recherche
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()) {
            //$criteres contient les réponses au formulaire
            $criteres = $rechercheForm->getData();
            //on fait appel à une methode perso dans le repository
            $sorties = $sortieRepo->findSortiesSelonRecherche($criteres);
            $this->addFlash("success", "Il y a ". sizeof($sorties)  ." sortie(s) correspondant à votre recherche !");
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
            $this->addFlash("alert", "Il n'y a plus de place pour la sortie " . $sortie->getNom());
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
    public function modification($id, EntityManagerInterface $em){
        //TODO
        //vérifier que l'utilisateur courant est bien l'organisateur de la sortie
    }

    /**
     * Annuler une sortie
     * @Route("/sortie/annulation/{id}", requirements={"id" = "\d+"}, name="annulation")
     * @param $id
     * @param EntityManagerInterface $em
     */
    public function annulation($id, EntityManagerInterface $em){
        //TODO
        //vérifier que l'utilisateur courant est bien l'organisateur de la sortie
    }

}
