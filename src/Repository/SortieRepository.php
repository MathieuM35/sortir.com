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
        $auMoinsUneCheckboxeCochee = false;
        if ($criteres['organisateur'] or $criteres['inscrit']
            or $criteres['nonInscrit'] or $criteres['sortiePassee']) {
            $auMoinsUneCheckboxeCochee = true;
        }
    dump($auMoinsUneCheckboxeCochee);
        $qb = $this->createQueryBuilder('s');

        $qb->leftJoin('s.organisateur', 'u');
        $qb->addSelect('u');

        $qb->leftJoin('u.site', 'si');
        $qb->addSelect('si');

        //on construit la requete selon les champs qui ont été complétés
        if ($criteres['site']) {

//            $qb->join('s.organisateur','u');
//            $qb->addSelect('u');
//            $qb->join('u.site','si');
//            $qb->addSelect('si');

            $qb->andWhere('si.id = :idSite');
            $qb->setParameter('idSite', $criteres['site']);
        }
        if ($criteres['nomContient']) {
            $qb->andWhere('s.nom LIKE :nomContient');
            $qb->setParameter('nomContient', '%' . $criteres['nomContient'] . '%');
        }
        if ($criteres['periodeDebut'] && $criteres['periodeFin']) {
            $qb->andWhere('s.dateHeureDebut BETWEEN :periodeDebut AND :periodeFin');
            $qb->setParameter('periodeDebut', $criteres['periodeDebut']);
            $qb->setParameter('periodeFin', $criteres['periodeFin']);
        }
        if ($criteres['periodeDebut'] && empty($criteres['periodeFin'])) {
            $qb->andWhere('s.dateHeureDebut > :periodeDebut');
            $qb->setParameter('periodeDebut', $criteres['periodeDebut']);
        }
        if ($criteres['periodeFin'] && empty($criteres['periodeDebut'])) {
            $qb->andWhere('s.dateHeureDebut < :periodeFin');
            $qb->setParameter('periodeFin', $criteres['periodeFin']);
        }

        //Gestion des checkboxes
        if ($criteres['organisateur']) {
            if ($auMoinsUneCheckboxeCochee) {
                $qb->orWhere('s.organisateur = :organisateur');
            } else {
                $qb->andWhere('s.organisateur = :organisateur');
            }
            $qb->setParameter('organisateur', $user);
        }
        if ($criteres['inscrit']) {
            $qb->join('s.participants', 'us');
            $qb->addSelect('us');
            if ($auMoinsUneCheckboxeCochee) {
                $qb->orWhere('us.id = :idUser');
            } else {
                $qb->andWhere('us.id = :idUser');
            }

            $qb->setParameter('idUser', $user);
        }
        if ($criteres['nonInscrit']) {
//TODO
//            $qb->join('s.participants','u');
//            $qb->addSelect('u');
//            $qb->andWhere('NOT u.id = :idUser');
//            $qb->setParameter('idUser',$user);
        }
        if ($criteres['sortiePassee']) {
            if ($auMoinsUneCheckboxeCochee) {
                $qb->orWhere('s.dateHeureDebut < :aujourdhui');
            } else {
                $qb->andWhere('s.dateHeureDebut < :aujourdhui');
            }
            $qb->setParameter('aujourdhui', new \DateTime());
        }

        //on affiche les sorties selon leur date
        $qb->orderBy('s.dateHeureDebut', 'ASC');

        //on effectue la requete
        $query = $qb->getQuery();
        $sorties = $query->getResult();
        return $sorties;
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
