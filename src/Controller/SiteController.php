<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\RechercheSiteType;
use App\Form\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends Controller
{
    /**
     * @Route("/site", name="site")
     */
    public function liste(EntityManagerInterface $em, Request $request)
    {
        $rechercheSiteForm = $this->createForm(RechercheSiteType::class);
        $rechercheSiteForm->handleRequest($request);

        $siteRepo = $this->getDoctrine()->getRepository(Site::class);
        $sites = $siteRepo->findAll();

        if ($rechercheSiteForm->isSubmitted() && $rechercheSiteForm->isValid()){
            if ($rechercheSiteForm->get('rechercher')->isClicked()){
                $nomSiteContient = $rechercheSiteForm->getData();
                $sites = $siteRepo->findByMotCle($nomSiteContient['nomContient']);
            }
            if ($rechercheSiteForm->get('voirTout')->isClicked()){
                $sites = $siteRepo->findAll();
            }
        }

        return $this->render('site/liste.html.twig', ['rechercheSiteForm'=>$rechercheSiteForm->createView(),
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

    /**
     * @Route("/site/show/{id}", name="site_show", methods={"GET"})
     */
    public function show(Site $site){
        return $this->render('site/show.html.twig', [
            'site' => $site,
        ]);
    }

    /**
     * @Route("/site/edit/{id}", name="site_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Site $site){
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('site', [
                'id' => $site->getId(),
            ]);
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/site/delete/{id}", name="site_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Site $site){
        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('site');
    }
}
