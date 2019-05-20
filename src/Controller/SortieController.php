<?php

namespace App\Controller;

use App\Entity\Sortie;
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
        $sortie->setEtat('Créée');
        $sortie->setOrganisateur($this->getUser());
        $sortie->setParticipants(array());

        $formSortie = $this->createForm(SortieType::class, $sortie);
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Votre sortie a bien été ajoutée !");
            return $this->redirectToRoute("details", ['id' => $sortie->getId()]);
        } else {
            return (['formSortie'=>$formSortie->createView()]);
        }


    }
}
