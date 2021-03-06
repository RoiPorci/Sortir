<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function findAllFiltered(?array $filter, User $user, int $page = 1, int $maxResults): array
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

            if(array_key_exists('isOrganiser', $filter)){
                $queryBuilder->andWhere('t.organiser = :user');
                $queryBuilder->setParameter(':user', $user);
            }

            if(array_key_exists('isParticipant', $filter)){
                $queryBuilder->andWhere(':user MEMBER OF t.participants');
                $queryBuilder->setParameter(':user', $user);
            }

            if(array_key_exists('isNotParticipant', $filter)){
                $queryBuilder->andWhere(':user NOT MEMBER OF t.participants');
                $queryBuilder->setParameter(':user', $user);
            }

            if(array_key_exists('past', $filter)){
                $queryBuilder->andWhere('t.dateTimeStart < :now');
            }
            else {
                $queryBuilder->andWhere('t.dateTimeStart > :now');

            }
            $queryBuilder->setParameter(':now', new \DateTime());
        }
        else
        {
            $queryBuilder->andWhere('t.dateTimeStart > :now');
            $queryBuilder->setParameter(':now', new \DateTime());
        }

        //Non archiv??es
        $now = new \DateTime();
        $dateArchived = $now->modify('-1 month');

        $queryBuilder->andWhere('t.dateTimeStart > :dateArchived');
        $queryBuilder->setParameter(':dateArchived', $dateArchived);

        //Mise en place de la pagination :
        $queryBuilder->select('COUNT(t)');
        $countQuery = $queryBuilder->getQuery();
        $totalTrips = $countQuery->getSingleScalarResult();

        //Mise en forme des r??sultats :
        $queryBuilder->addOrderBy('t.dateTimeStart', 'DESC');

        //Mise en place de la pagination :
        $offset = ($page - 1) * $maxResults;
        $queryBuilder->setMaxResults($maxResults);
        $queryBuilder->setFirstResult($offset);

        //On effectue la requ??te pour r??cup??rer la liste des Sorties
        $queryBuilder->select('t');

        //On ajoute des jointures pour ??viter les multiples requ??tes par Doctrine
        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->join('t.state', 's');
        $queryBuilder->addSelect('s');

        $queryBuilder->leftJoin('t.participants', 'p');
        $queryBuilder->addSelect('p');

        $queryBuilder->join('t.organiserCampus', 'c');
        $queryBuilder->addSelect('c');

        //R??cup??ration des Sorties filtr??es
        $query = $queryBuilder->getQuery();
        $trips = new Paginator($query);

        return [
            'totalTrips' => $totalTrips,
            'trips' => $trips
        ];
    }

    /**
     * @param $id
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findNotArchived($id)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $queryBuilder->andWhere('t.id = :id');
        $queryBuilder->setParameter('id', $id);

        //Non archiv??e
        $now = new \DateTime();
        $dateArchived = $now->modify('-1 month');

        $queryBuilder->andWhere('t.dateTimeStart > :dateArchived');
        $queryBuilder->setParameter(':dateArchived', $dateArchived);

        //On ajoute des jointures pour ??viter les multiples requ??tes par Doctrine
        $queryBuilder->join('t.organiserCampus', 'c');
        $queryBuilder->addSelect('c');

        $queryBuilder->join('t.location', 'l');
        $queryBuilder->addSelect('l');

        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->leftJoin('t.participants', 'p');
        $queryBuilder->addSelect('p');

        $queryBuilder->join('l.city', 'lc');
        $queryBuilder->addSelect('lc');

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @return int|mixed|string
     */
    public function findAllNotArchived()
    {
        $queryBuilder = $this->createQueryBuilder('t');

        //Non archiv??es
        $now = new \DateTime();
        $dateArchived = $now->modify('-1 month');

        $queryBuilder->andWhere('t.dateTimeStart > :dateArchived');
        $queryBuilder->setParameter(':dateArchived', $dateArchived);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * @param $id
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findWithStateAndParticipants($id)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $queryBuilder->andWhere('t.id = :id');
        $queryBuilder->setParameter('id', $id);

        //Non archiv??e
        $now = new \DateTime();
        $dateArchived = $now->modify('-1 month');

        $queryBuilder->andWhere('t.dateTimeStart > :dateArchived');
        $queryBuilder->setParameter(':dateArchived', $dateArchived);

        //On ajoute des jointures pour ??viter les multiples requ??tes par Doctrine
        $queryBuilder->join('t.state', 's');
        $queryBuilder->addSelect('s');

        $queryBuilder->leftJoin('t.participants', 'p');
        $queryBuilder->addSelect('p');

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * @param int $id
     * @param UserInterface $organiser
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findWithoutParticipants(int $id, UserInterface $organiser)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $queryBuilder->andWhere('t.id = :id');
        $queryBuilder->setParameter('id', $id);

        $queryBuilder->andWhere('t.organiser = :organiser');
        $queryBuilder->setParameter('organiser', $organiser);

        //Non archiv??e
        $now = new \DateTime();
        $dateArchived = $now->modify('-1 month');

        $queryBuilder->andWhere('t.dateTimeStart > :dateArchived');
        $queryBuilder->setParameter(':dateArchived', $dateArchived);

        //On ajoute des jointures pour ??viter les multiples requ??tes par Doctrine
        $queryBuilder->join('t.organiserCampus', 'c');
        $queryBuilder->addSelect('c');

        $queryBuilder->join('t.organiser', 'o');
        $queryBuilder->addSelect('o');

        $queryBuilder->join('t.location', 'l');
        $queryBuilder->addSelect('l');

        $queryBuilder->join('l.city', 'lc');
        $queryBuilder->addSelect('lc');

        $queryBuilder->join('t.state', 's');
        $queryBuilder->addSelect('s');

        $query = $queryBuilder->getQuery();
        $result = $query->getOneOrNullResult();

        return $result;
    }

}
