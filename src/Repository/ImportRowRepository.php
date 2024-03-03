<?php

namespace App\Repository;

use App\Entity\ImportRow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImportRow>
 *
 * @method ImportRow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportRow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportRow[]    findAll()
 * @method ImportRow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportRowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportRow::class);
    }

    //    /**
    //     * @return ImportRow[] Returns an array of ImportRow objects
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

    //    public function findOneBySomeField($value): ?ImportRow
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
