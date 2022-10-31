<?php

namespace App\Repository;

use App\Entity\StructureParameters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StructureParameters>
 *
 * @method StructureParameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method StructureParameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method StructureParameters[]    findAll()
 * @method StructureParameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StructureParametersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StructureParameters::class);
    }

    public function add(StructureParameters $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StructureParameters $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStructureId($value): ?StructureParameters
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.structure_id = :structure_id')
            ->setParameter('structure_id', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByFranchiseId($value): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.franchise_id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllStructuresIDOfAnFranchise(int $id): array
    {
        $structuresParams = $this->createQueryBuilder('s')
            ->andWhere('s.franchise_id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
        ;

        $structuresIds = array();

        foreach($structuresParams as $structure){
            array_push($structuresIds, $structure->getStructureId());
        }


        return $structuresIds;
    }


//    /**
//     * @return StructureParameters[] Returns an array of StructureParameters objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StructureParameters
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
