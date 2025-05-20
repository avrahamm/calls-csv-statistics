<?php

namespace App\Repository;

use App\Entity\CustomerCallStatistic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerCallStatistic>
 *
 * @method CustomerCallStatistic|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerCallStatistic|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerCallStatistic[]    findAll()
 * @method CustomerCallStatistic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerCallStatisticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerCallStatistic::class);
    }

    public function save(CustomerCallStatistic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomerCallStatistic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}