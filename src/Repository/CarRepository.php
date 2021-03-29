<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    // /**
    //  * @return Car[] Returns an array of Car objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Car
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    //*** LEFT JOIN WİTH SQL **************
    public function getAllCars(): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql='
       SELECT h.*,c.title as catname,u.name, u.surname FROM car h 
       JOIN category c ON c.id = h.category_id 
       JOIN user u ON u.id = h.userid 
       ORDER BY c.title ASC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        //returns an array of arrays (i.e a raw data set)
        return $stmt->fetchAll();
    }
}
