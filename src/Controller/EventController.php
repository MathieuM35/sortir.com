<?php

namespace App\Controller;

use App\Entity\Lieu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends Controller
{
    /**
     * @Route("/creer-sortie/requeteAjax", name="requeteAjax")
     */
    public function requeteAjax(Request $request, EntityManagerInterface $em)
    {
        $select = $request->request->get('choix');
        $lieux = $em->getRepository(Lieu::class)->findBy(array('ville' => $select));

        $lieuTab = [];
        foreach ($lieux as $lieu){
            $lieuTab[$lieu->getId()] = $lieu->getNom();
        }

        $response = new Response(json_encode($lieuTab));
        $response->headers->set('Content-type', 'application/json');

        return $response;
    }

    /**
     * @Route("/creer-sortie/requeteLieu", name="requeteLieu")
     */
    public function requeteLieu(Request $request, EntityManagerInterface $em){
        $infoLieu = $request->request->get('detailLieu');
        $detail = $em->getRepository(Lieu::class)->find($infoLieu);

        $lieu = [
            'rue' => $detail->getRue(),
            'cp' => $detail->getVille()->getCodePostal(),
            'latitude' => $detail->getLatitude(),
            'longitude' => $detail->getLongitude(),
        ];

        $response = new Response(json_encode($lieu));
        $response->headers->set('Content-type', 'application/json');

        return $response;
    }

}
