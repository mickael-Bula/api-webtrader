<?php

namespace App\Repository;

use App\Entity\Cac;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cac>
 *
 * @method Cac|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cac|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cac[]    findAll()
 * @method Cac[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CacRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cac::class);
    }

    //    /**
    //     * @return Cac[] Returns an array of Cac objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cac
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
