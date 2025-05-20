<?php

namespace App\Repository;

use App\Entity\ContinentPhonePrefix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContinentPhonePrefix>
 *
 * @method ContinentPhonePrefix|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContinentPhonePrefix|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContinentPhonePrefix[]    findAll()
 * @method ContinentPhonePrefix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContinentPhonePrefixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContinentPhonePrefix::class);
    }

    public function save(ContinentPhonePrefix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContinentPhonePrefix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}