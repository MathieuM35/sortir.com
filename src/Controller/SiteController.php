<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends Controller
{
    /**
     * @Route("/site", name="site")
     */
    public function liste()
    {
        $siteRepo = $this->getDoctrine()->getRepository(Site::class);
        $sites = $siteRepo->findAll();
        return $this->render('site/liste.html.twig', [
            'controller_name' => 'SiteController',
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/site/add", name="site_add")
     */
    public function ajouterSite(EntityManagerInterface $em, Request $request){
        $site = new Site();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);
        if ($siteForm->isSubmitted() && $siteForm->isValid()){
            $em->persist($site);
            $em->flush();
            $this->addFlash("success", "Le site a bien été créé !");
            return $this->redirectToRoute('site',[]);
        }
        return $this->render('site/add.html.twig', ["siteForm"=>$siteForm->createView()]);
    }
}
