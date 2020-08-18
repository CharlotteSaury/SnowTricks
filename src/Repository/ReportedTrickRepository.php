<?php

namespace App\Repository;

use App\Entity\ReportedTrick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportedTrick|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportedTrick|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportedTrick[]    findAll()
 * @method ReportedTrick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportedTrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportedTrick::class);
    }

    // /**
    //  * @return ReportedTrick[] Returns an array of ReportedTrick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReportedTrick
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
