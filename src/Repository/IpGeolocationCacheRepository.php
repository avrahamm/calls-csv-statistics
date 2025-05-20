<?php

namespace App\Repository;

use App\Entity\IpGeolocationCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpGeolocationCache>
 *
 * @method IpGeolocationCache|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpGeolocationCache|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpGeolocationCache[]    findAll()
 * @method IpGeolocationCache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpGeolocationCacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpGeolocationCache::class);
    }

    public function save(IpGeolocationCache $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IpGeolocationCache $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}