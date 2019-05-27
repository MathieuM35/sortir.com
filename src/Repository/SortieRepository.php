<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * Traite les critères de recherche et retourne un tableau de sorties correspondants à ces critères
     * @param $criteres criteres de recherche
     * @param $user utilisateur connecté
     * @return mixed tableau des sorties correspondant aux critères de recherche
     */
    public function findSortiesSelonRecherche($criteres, $user)
    {

        $champsRestrictif = false;
        if ($criteres['site'] or $criteres['nomContient'] or $criteres['periodeDebut'] or $criteres['periodeFin']) {
            $champsRestrictif = true;
        }

        $auMoinsUneCheckboxeCochee = false;
        if ($criteres['organisateur'] or $criteres['inscrit']
            or $criteres['nonInscrit'] or $criteres['sortiePassee']) {
            $auMoinsUneCheckboxeCochee = true;
        }

        $em = $this->getEntityManager();

        $dql = <<<DQL
SELECT s, u
FROM App\Entity\Sortie s
JOIN s.participants u
JOIN u.site si
JOIN s.etat e
WHERE
DQL;

        if ($champsRestrictif) {
            if ($criteres['site']) {
                $dql .= " si.id = :idSite AND ";
            }
            if ($criteres['nomContient']) {
                $dql .= " s.nom LIKE :nomContient AND";
            }
            if ($criteres['periodeDebut'] && $criteres['periodeFin']) {
                $dql .= " s.dateHeureDebut BETWEEN :periodeDebut AND :periodeFin AND";
            }
            if ($criteres['periodeDebut'] && empty($criteres['periodeFin'])) {
                $dql .= " s.dateHeureDebut > :periodeDebut AND";
            }
            if ($criteres['periodeFin'] && empty($criteres['periodeDebut'])) {
                $dql .= " s.dateHeureDebut < :periodeFin AND";
            }
        }

//        $dql .= " s.id IS NOT NULL";
        $dql .= " e.id != 7";

        //GESTION CHECKBOXES

        if ($auMoinsUneCheckboxeCochee) {
            $dql .= " AND(";
        }

        if ($criteres['organisateur']) {
            $dql .= " s.organisateur = :organisateur";
            if($criteres['inscrit'] or $criteres['nonInscrit'] or $criteres['sortiePassee']== true){
                $dql .= " OR ";
            } else {
                $dql .= " )";
            }
        }

        if ($criteres['inscrit']) {
            $dql .= " u.id = :idUser ";
            if($criteres['nonInscrit'] or $criteres['sortiePassee']){
                $dql .= " OR ";
            } else {
                $dql .= " )";
            }
        }

        if($criteres['nonInscrit']){
            $dql .= " s.id NOT IN (SELECT so
                    FROM App\Entity\Sortie so
                    JOIN so.participants us
                    JOIN us.site sit
                    WHERE us.id = :idUser)";
            if($criteres['sortiePassee']){
                $dql .= " OR ";
            } else {
                $dql .= " )";
            }
        }

        if ($criteres['sortiePassee']) {
            $dql .= " s.dateHeureDebut < :aujourdhui)";
        }

        $dql .= " ORDER BY s.dateHeureDebut ASC";

        //on créer la query
        $query = $em->createQuery($dql);


        //on set les paramètres à la query
        if ($criteres['site']) {
            $query->setParameter('idSite', $criteres['site']->getId());
        }
        if ($criteres['nomContient']) {
            $query->setParameter('nomContient', '%' . $criteres['nomContient'] . '%');
        }
        if ($criteres['periodeDebut'] && $criteres['periodeFin']) {
            $query->setParameter('periodeDebut', $criteres['periodeDebut']);
            $query->setParameter('periodeFin', date_add($criteres['periodeFin'], date_interval_create_from_date_string('1 day')));
        }
        if ($criteres['periodeDebut'] && empty($criteres['periodeFin'])) {
            $query->setParameter('periodeDebut', $criteres['periodeDebut']);
        }
        if ($criteres['periodeFin'] && empty($criteres['periodeDebut'])) {
            $query->setParameter('periodeFin', date_add($criteres['periodeFin'], date_interval_create_from_date_string('1 day')));
        }

        //on set les parametres liés aux checkboxes
        if ($criteres['organisateur']) {
            $query->setParameter('organisateur', $user);
        }
        if ($criteres['inscrit']) {
            $query->setParameter('idUser', $user);
        }
        if ($criteres['nonInscrit']) {
            $query->setParameter('idUser', $user);
        }

        if ($criteres['sortiePassee']) {
            $query->setParameter('aujourdhui', new \DateTime());
        }

        $sorties = $query->getResult();
        return $sorties;

        //Gestion des checkboxes 23/04
//        if ($criteres['organisateur']) {
//            if ($champsRestrictif) {
//                $qb->andWhere('s.organisateur = :organisateur');
//            } else {
//                $qb->orWhere('s.organisateur = :organisateur');
//            }
//            $qb->setParameter('organisateur', $user);
//        }
//        if ($criteres['inscrit']) {
//            $qb->join('s.participants', 'us');
//            $qb->addSelect('us');
//            if ($champsRestrictif) {
//                $qb->andWhere('us.id = :idUser');
//            } else {
//                $qb->orWhere('us.id = :idUser');
//            }
//            $qb->setParameter('idUser', $user);
//        }
//        if ($criteres['nonInscrit']) {
////TODO
////            $qb->join('s.participants','u');
////            $qb->addSelect('u');
////            $qb->andWhere('NOT u.id = :idUser');
////            $qb->setParameter('idUser',$user);
//        }
//        if ($criteres['sortiePassee']) {
//            if ($champsRestrictif) {
//                $qb->andWhere('s.dateHeureDebut < :aujourdhui');
//            } else {
//                $qb->orWhere('s.dateHeureDebut < :aujourdhui');
//            }
//            $qb->setParameter('aujourdhui', new \DateTime());
//        }

//        //on affiche les sorties selon leur date
//        $qb->orderBy('s.dateHeureDebut', 'ASC');
//
//        //on effectue la requete
//        $query = $qb->getQuery();
//        $sorties = $query->getResult();
//        return $sorties;
    }

    public function findParticipantsParSortie($idSortie = 13)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT participants
            FROM App\Entity\Sortie s
            WHERE s.id = 13";
        $query = $em->createQuery($dql);
        $results = $query->getResult();
        return $results;

    }


    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
