<?php

namespace App\Repository;

use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Groupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Groupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Groupe[]    findAll()
 * @method Groupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Groupe::class);
    }

    public function findGroupesByCreateur($user)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.createur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function selectGroupesByCreateurForSortieType($user)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.createur = :user')
            ->setParameter('user', $user);
    }

    public function getAllMembresByGroupId($id)
    {

        $em = $this->getEntityManager();

        $dql = "SELECT u.id
        FROM App\Entity\Groupe g
        JOIN g.membres u
        WHERE g.id = :gpeId";

        $query = $em->createQuery($dql);

        $query->setParameter('gpeId', $id);

        $membres = $query->getResult();
        return $membres;



//        $query = $em->createQuery('SELECT u FROM MyProject\Model\User u WHERE u.age > 20');
//        return $this->createQueryBuilder('g')
//            ->select('g.membres')
//            ->from(Groupe::class,'groupe')
//            ->andWhere('g.id = :gpeId')
//            ->setParameter('gpeId',$id)
//            ->getQuery()
//            ->getResult()
//            ;
    }

    // /**
    //  * @return Groupe[] Returns an array of Groupe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Groupe
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
