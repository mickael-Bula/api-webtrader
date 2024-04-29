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

    /**
     * @return string|null Retourne la date la plus récente enregistrée en base
     */
    public function getMaxCreatedAt(): ?string
    {
        return $this->createQueryBuilder('c')
            ->select('MAX(c.createdAt)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
