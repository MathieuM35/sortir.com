<?php


namespace App\Controller;


use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends Controller
{
    /**
     * @Route("/ville", name="ville")
     */
    public function liste()
    {
        $villeRepo = $this->getDoctrine()->getRepository(Ville::class);
        $villes = $villeRepo->findAll();
        return $this->render('ville/liste.html.twig', [
            'controller_name' => 'VilleController',
            'villes' => $villes,
        ]);
    }

    /**
     * @Route("/ville/add", name="ville_add")
     */
    public function ajouterVille(EntityManagerInterface $em, Request $request){
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class,$ville);
        $villeForm->handleRequest($request);
        if ($villeForm->isSubmitted() && $villeForm->isValid()){
            $em->persist($ville);
            $em->flush();
            $this->addFlash("success", "La ville a bien été créé !");
            return $this->redirectToRoute('ville',[]);
        }
        return $this->render('ville/add.html.twig', ["villeForm"=>$villeForm->createView()]);
    }
}