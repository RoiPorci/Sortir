<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    /**
     * @param array|null $filter
     * @param User $user
     * @param int $page
     * @param int $maxResults
     * @return array
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findTripsFiltered(?array $filter, User $user, int $page = 1, int $maxResults): array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        //Application du filtre
        if($filter){
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
                $queryBuilder->andWhere('t.organiser = :user');
                $queryBuilder->setParameter(':user', $user);
            }

            if($filter['isParticipant']){
                $queryBuilder->andWhere(':user MEMBER OF t.participants');
                $queryBuilder->setParameter(':user', $user);
            }

            if($filter['isNotParticipant']){
                $queryBuilder->andWhere(':user NOT MEMBER OF t.participants');
                $queryBuilder->setParameter(':user', $user);
            }

            if($filter['past']){
                $queryBuilder->andWhere('t.dateTimeStart < :now');
            }
            else {
                $queryBuilder->andWhere('t.dateTimeStart > :now');

            }
            $queryBuilder->setParameter(':now', new \DateTime());
        }

        //On récupère le nombre de Sorties filtrées
        $queryBuilder->select('COUNT(t)');
        $countQuery = $queryBuilder->getQuery();
        $nbTrips = $countQuery->getSingleScalarResult();

        //Mise en forme des résultats :
        $queryBuilder->addOrderBy('t.dateTimeStart', 'DESC');

        //On met en place la pagination
        $offset = ($page - 1) * $maxResults;

        $queryBuilder->setMaxResults($maxResults);
        $queryBuilder->setFirstResult($offset);

        //On effectue la requête pour récupérer la liste des Sorties
        $queryBuilder->select('t');

        //On ajoute des jointures pour éviter les multiples requêtes par Doctrine
        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->join('t.state', 's');
        $queryBuilder->addSelect('s');

        $queryBuilder->join('t.participants', 'p');
        $queryBuilder->addSelect('p');

        $queryBuilder->join('t.organiserCampus', 'c');
        $queryBuilder->addSelect('c');

        //Récupération des Sorties filtrées
        $query = $queryBuilder->getQuery();
        $trips = new Paginator($query);

        return [
            'nbTrips' => $nbTrips,
            'trips' => $trips
        ];
    }

    public function findATrip($id)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $queryBuilder->andWhere('t.id = :id');
        $queryBuilder->setParameter('id', $id);

        $queryBuilder->join('t.organiserCampus', 'c');
        $queryBuilder->addSelect('c');

        $queryBuilder->join('t.location', 'l');
        $queryBuilder->addSelect('l');

        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->join('t.participants', 'p');
        $queryBuilder->addSelect('p');

        $queryBuilder->join('l.city', 'lc');
        $queryBuilder->addSelect('lc');

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
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
