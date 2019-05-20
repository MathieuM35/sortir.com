<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

    public function add(){

    }
}
