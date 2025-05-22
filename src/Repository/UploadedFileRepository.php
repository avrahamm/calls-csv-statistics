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
     */
    public function updatePhonesEnriched(int $fileId): bool
    {
        try {
            $em = $this->getEntityManager();

            // Get the entity with a lock
            $file = $this->find($fileId, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            if (!$file) {
                return false;
            }

            // Update the field
            $file->setPhonesEnriched(new \DateTime());

            // Save the changes
            $em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update ips_enriched timestamp with pessimistic locking to avoid race conditions
     */
    public function updateIpsEnriched(int $fileId): bool
    {
        try {
            $em = $this->getEntityManager();

            // Get the entity with a lock
            $file = $this->find($fileId, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            if (!$file) {
                return false;
            }

            // Update the field
            $file->setIpsEnriched(new \DateTime());

            // Save the changes
            $em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
