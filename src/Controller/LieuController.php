<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends Controller
{
    /**
     * @Route("/lieu/add", name="lieu_add")
     */
    public function ajouterLieu(EntityManagerInterface $em, Request $request)
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);
        if ($lieuForm->isSubmitted() && $lieuForm->isValid()){
            $em->persist($lieu);
            $em->flush();
            $this->addFlash("success", "Le lieu a bien été créé !");
            return $this->redirectToRoute('creer_sortie', []);
        }

        return $this->render('lieu/add.html.twig', ['lieuForm' => $lieuForm->createView()]);
    }
}
