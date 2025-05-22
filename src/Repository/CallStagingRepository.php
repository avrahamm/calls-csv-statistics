<?php

namespace App\Repository;

use App\Entity\CallStaging;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CallStaging>
 *
 * @method CallStaging|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallStaging|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallStaging[]    findAll()
 * @method CallStaging[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallStagingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallStaging::class);
    }

    public function save(CallStaging $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CallStaging $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all records by batch ID
     */
    public function findByBatchId(string $batchId): array
    {
        return $this->createQueryBuilder('cs')
            ->andWhere('cs.batch_id = :batchId')
            ->setParameter('batchId', $batchId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if there are any invalid records for a batch
     */
    public function hasInvalidRecords(string $batchId): bool
    {
        $count = $this->createQueryBuilder('cs')
            ->select('COUNT(cs.id)')
            ->andWhere('cs.batch_id = :batchId')
            ->andWhere('cs.is_valid = :isValid')
            ->setParameter('batchId', $batchId)
            ->setParameter('isValid', false)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Transfer valid records from staging to the final table
     */
    public function transferToCalls(string $batchId): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            INSERT INTO calls (
                customer_id, call_date, duration, dialed_number, source_ip, 
                source_continent, dest_continent, within_same_cont, uploaded_file_id
            )
            SELECT 
                customer_id, call_date, duration, dialed_number, source_ip, 
                source_continent, dest_continent, within_same_cont, uploaded_file_id
            FROM calls_staging
            WHERE batch_id = :batchId
            AND (is_valid = true OR is_valid IS NULL)
        ';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('batchId', $batchId);
        $rowCount = $stmt->executeStatement();

        return $rowCount;
    }

    /**
     * Delete all records for a batch
     */
    public function deleteByBatchId(string $batchId): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'DELETE FROM calls_staging WHERE batch_id = :batchId';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('batchId', $batchId);
        $rowCount = $stmt->executeStatement();

        return $rowCount;
    }
}
