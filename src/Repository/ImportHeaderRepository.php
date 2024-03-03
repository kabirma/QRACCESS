<?php

namespace App\Repository;

use App\Entity\ImportHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImportHeader>
 *
 * @method ImportHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportHeader[]    findAll()
 * @method ImportHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportHeader::class);
    }

    //    /**
    //     * @return ImportHeader[] Returns an array of ImportHeader objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ImportHeader
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
