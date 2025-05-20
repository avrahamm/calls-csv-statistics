<?php

namespace App\Repository;

use App\Entity\Call;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Call>
 *
 * @method Call|null find($id, $lockMode = null, $lockVersion = null)
 * @method Call|null findOneBy(array $criteria, array $orderBy = null)
 * @method Call[]    findAll()
 * @method Call[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    public function save(Call $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Call $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}