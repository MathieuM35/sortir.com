<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainController
 * @package App\Controller
 */
class MainController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {


        return $this->render('main/home.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
