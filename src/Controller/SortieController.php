<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends Controller
{
    /**
     * @Route("/sortie", name="sortie")
     */
    public function index()
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
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
        $sortie->setEtat($etatRepo->find(1));
        $sortie->setOrganisateur($this->getUser());
        $sortie->setParticipants(array());

        $villes = $em->getRepository(Ville::class)->findAll();

        $formSortie = $this->createForm(SortieType::class, $sortie, array('villes'=>$villes));
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Votre sortie a bien été ajoutée !");
            return $this->redirectToRoute("liste_sorties");
        } else {
            return (['formSortie'=>$formSortie->createView()]);
        }
    }

    /**
     * @param $id id la sortie à afficher
     * @Route("/sortie/{id}", name="sortie_details")
     */
    public function details($id){
        //on récupère la sortie selon l'id en paramèrte
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        //si aucune sortie n'est trouvée avec cet idée, on lève une exception
        if(empty($sortie)){
            throw  $this->createNotFoundException("Cette sortie n'existe pas");
        }

        return $this->render('sortie/details.html.twig',['sortie'=>$sortie]);
    }
}
