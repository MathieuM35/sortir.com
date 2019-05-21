<?php

namespace App\Controller;

use App\Entity\Sortie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    /**
     * @Route("/main", name="main")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    /**
     * Afficher sur la page d'accueil l'ensemble de toutes les sorties présentes en BDD
     * @Route("/",name="liste_sorties")
     * @Template()
     */
    public function listeToutesSorties(){
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();
        return (['sorties'=>$sorties]);

    }
}
