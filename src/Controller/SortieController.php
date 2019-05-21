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
            if($formSortie->get('enregister')->isClicked()){
                $sortie->setEtat($etatRepo->find(1));
            }
            //si clic sur le bouton Publier on set l'état 'Ouverte'
            if($formSortie->get('publier')->isClicked()){
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
}
