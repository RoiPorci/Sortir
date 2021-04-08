<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trip[]    findAll()
 * @method Trip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findTripsFiltered($filter, User $user){
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t');

        //Application du filtre
        if($filter['campus']){
            $queryBuilder->andWhere('t.organiserCampus = :campus');
            $queryBuilder->setParameter(':campus', $filter['campus']);
        }

        if($filter['name']){
            $queryBuilder->andWhere('t.name LIKE :name');
            $queryBuilder->setParameter('name', '%'.$filter['name'].'%');
        }

        if ($filter['dateStart']){
            $queryBuilder->andWhere('t.dateTimeStart >= :dateStart');
            $queryBuilder->setParameter(':dateStart', $filter['dateStart']);
        }

        if ($filter['dateEnd']){
            $queryBuilder->andWhere('t.dateTimeStart <= :dateEnd');
            $queryBuilder->setParameter(':dateEnd', $filter['dateEnd']);
        }

        if($filter['isOrganiser']){
            if($filter['isParticipant'] || $filter['isNotParticipant']){
                $queryBuilder->orWhere('t.organiser = :user');
            }
            else {
                $queryBuilder->andWhere('t.organiser = :user');
            }
        }
        else {
            $queryBuilder->andWhere('t.organiser != :user');
        }

        if($filter['isParticipant']){
            if($filter['isOrganiser'] || $filter['isNotParticipant']){
                $queryBuilder->orWhere(':user MEMBER OF t.participants');
            }
            else {
                $queryBuilder->andWhere(':user MEMBER OF t.participants');
            }

        }

        if($filter['isNotParticipant']){
            if($filter['isOrganiser'] || $filter['isParticipant']){
                $queryBuilder->orWhere(':user NOT MEMBER OF t.participants');
            }
            else {
                $queryBuilder->andWhere(':user NOT MEMBER OF t.participants');
            }
        }

        $queryBuilder->setParameter(':user', $user);


        if($filter['past']){
            $queryBuilder->andWhere('t.dateTimeStart < :now');
        }
        else {
            $queryBuilder->andWhere('t.dateTimeStart > :now');

        }
        $queryBuilder->setParameter(':now', new \DateTime());


        //On ajoute des jointures pour éviter les mutltiples requêtes par Doctrine
        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->join('t.state', 's');
        $queryBuilder->addSelect('s');

        //Mise en forme des résultats :
        $queryBuilder->addOrderBy('t.dateTimeStart', 'DESC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    // /**
    //  * @return Trip[] Returns an array of Trip objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trip
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
