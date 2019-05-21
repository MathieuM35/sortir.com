<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @return mixed tableau des sorties correspondant aux critères de recherche
     */
    public function findSortiesSelonRecherche($criteres)
    {
        $qb = $this->createQueryBuilder('s');

        //on construit la requete selon les champs qui ont été complétés
        if ($criteres['nomContient']) {
            $qb->andWhere('s.nom LIKE :nomContient');
            $qb->setParameter('nomContient', '%' . $criteres['nomContient'] . '%');
        }
        if ($criteres['periodeDebut'] && $criteres['periodeFin']) {
            $qb->andWhere('s.dateHeureDebut BETWEEN :periodeDebut AND :periodeFin');
            $qb->setParameter('periodeDebut', $criteres['periodeDebut']);
            $qb->setParameter('periodeFin',$criteres['periodeFin']);
        }
        if ($criteres['periodeDebut'] && empty($criteres['periodeFin'])) {
            $qb->andWhere('s.dateHeureDebut > :periodeDebut');
            $qb->setParameter('periodeDebut', $criteres['periodeDebut']);
        }
        if ($criteres['periodeFin'] && empty($criteres['periodeDebut'])) {
            $qb->andWhere('s.dateHeureDebut < :periodeFin');
            $qb->setParameter('periodeFin', $criteres['periodeFin']);
        }
        if ($criteres['organisateur']) {
            //TODO
        }
        if ($criteres['inscrit']) {
            //TODO
        }
        if ($criteres['nonInscrit']) {
            //TODO
        }
        if ($criteres['sortiePassee']) {
            $qb->andWhere('s.dateHeureDebut < :aujourdhui');
            $qb->setParameter('aujourdhui', new \DateTime());
        }

        //on effectue la requete
        $query = $qb->getQuery();
        $sorties = $query->getResult();
        return $sorties;
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
