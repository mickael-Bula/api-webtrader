<?php

namespace App\Repository;

use App\Entity\Lvc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lvc>
 *
 * @method Lvc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lvc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lvc[]    findAll()
 * @method Lvc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LvcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lvc::class);
    }

    /**
     * @return string|null Retourne la date la plus récente enregistrée en base
     */
    public function getMaxCreatedAt(): ?string
    {
        return $this->createQueryBuilder('l')
            ->select('MAX(l.createdAt)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
