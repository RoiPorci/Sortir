<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @param int $id
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findCityWithLocations(int $id){
        $queryBuilder = $this->createQueryBuilder('c');

        $queryBuilder->andWhere('c.id = :id');
        $queryBuilder->setParameter(':id', $id);

        $queryBuilder->leftJoin('c.locations', 'l');
        $queryBuilder->addSelect('l');

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

}
