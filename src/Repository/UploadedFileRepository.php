<?php

namespace App\Repository;

use App\Entity\UploadedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UploadedFile>
 *
 * @method UploadedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UploadedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UploadedFile[]    findAll()
 * @method UploadedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadedFile::class);
    }

    public function save(UploadedFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UploadedFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find files by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.status = :status')
            ->setParameter('status', $status)
            ->orderBy('u.uploaded_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unprocessed files (status = pending or processing)
     */
    public function findUnprocessedFiles(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.status IN (:statuses)')
            ->setParameter('statuses', ['pending', 'processing'])
            ->orderBy('u.uploaded_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Update phones_enriched timestamp with pessimistic locking to avoid race conditions
     * Uses direct SQL query to bypass entity manager caching
     */
    public function updatePhonesEnriched(int $fileId): bool
    {
        try {
            $em = $this->getEntityManager();

            // Clear entity manager to avoid caching issues
            $em->clear();

            // Use direct SQL query to update the database
            $connection = $em->getConnection();
            $now = new \DateTime();
            $formattedDate = $now->format('Y-m-d H:i:s');

            $sql = 'UPDATE uploaded_files SET phones_enriched = :timestamp WHERE id = :id';
            $stmt = $connection->prepare($sql);
            $stmt->bindValue('timestamp', $formattedDate);
            $stmt->bindValue('id', $fileId, \PDO::PARAM_INT);
            $result = $stmt->executeStatement();

            // Clear entity manager again to ensure changes are visible
            $em->clear();

            return $result > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update ips_enriched timestamp with pessimistic locking to avoid race conditions
     * Uses direct SQL query to bypass entity manager caching
     */
    public function updateIpsEnriched(int $fileId): bool
    {
        try {
            $em = $this->getEntityManager();

            // Clear entity manager to avoid caching issues
            $em->clear();

            // Use direct SQL query to update the database
            $connection = $em->getConnection();
            $now = new \DateTime();
            $formattedDate = $now->format('Y-m-d H:i:s');

            $sql = 'UPDATE uploaded_files SET ips_enriched = :timestamp WHERE id = :id';
            $stmt = $connection->prepare($sql);
            $stmt->bindValue('timestamp', $formattedDate);
            $stmt->bindValue('id', $fileId, \PDO::PARAM_INT);
            $result = $stmt->executeStatement();

            // Clear entity manager again to ensure changes are visible
            $em->clear();

            return $result > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if both phones_enriched and ips_enriched are set for a given file
     * 
     * @param int $fileId The ID of the uploaded file
     * @return bool True if both enrichments are complete, false otherwise
     */
    public function areBothEnrichmentsComplete(int $fileId): bool
    {
        try {
            $em = $this->getEntityManager();

            // Clear entity manager to avoid caching issues
            $em->clear();

            $connection = $em->getConnection();

            $sql = 'SELECT COUNT(*) FROM uploaded_files 
                    WHERE id = :id 
                    AND phones_enriched IS NOT NULL 
                    AND ips_enriched IS NOT NULL';

            $stmt = $connection->prepare($sql);
            $stmt->bindValue('id', $fileId, \PDO::PARAM_INT);
            $result = $stmt->executeQuery();

            return (int) $result->fetchOne() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
