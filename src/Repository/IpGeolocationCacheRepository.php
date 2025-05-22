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

    /**
     * Find continent codes for multiple IP addresses in bulk
     * 
     * @param array $ips Array of IP addresses to look up
     * @return array Associative array of IP addresses and their continent codes
     */
    public function findContinentCodesForIps(array $ips): array
    {
        if (empty($ips)) {
            return [];
        }

        $connection = $this->getEntityManager()->getConnection();

        $placeholders = implode(',', array_fill(0, count($ips), '?'));

        $sql = "
            SELECT ip_address, continent_code
            FROM ip_geolocation_cache
            WHERE ip_address IN ($placeholders)
        ";

        $stmt = $connection->executeQuery($sql, $ips);

        $result = [];
        while ($row = $stmt->fetchAssociative()) {
            $result[$row['ip_address']] = $row['continent_code'];
        }

        return $result;
    }

    /**
     * Insert multiple IP geolocation cache entries in bulk
     * 
     * @param array $ipContinentData Associative array of IP addresses and their continent codes
     * @return int Number of inserted records
     */
    public function insertBulk(array $ipContinentData): int
    {
        if (empty($ipContinentData)) {
            return 0;
        }

        $connection = $this->getEntityManager()->getConnection();
        $now = new \DateTime();
        $nowFormatted = $now->format('Y-m-d H:i:s');

        try {
            // Begin transaction
            $connection->beginTransaction();

            // Prepare the SQL for bulk insert
            $insertSql = 'INSERT INTO ip_geolocation_cache (ip_address, continent_code, last_checked) VALUES ';
            $insertValues = [];
            $params = [];
            $types = [];

            $i = 0;
            foreach ($ipContinentData as $ip => $continent) {
                $ipParam = 'ip_' . $i;
                $continentParam = 'continent_' . $i;

                $insertValues[] = "(:$ipParam, :$continentParam, :last_checked)";
                $params[$ipParam] = $ip;
                $params[$continentParam] = $continent;
                $types[$ipParam] = \PDO::PARAM_STR;
                $types[$continentParam] = \PDO::PARAM_STR;
                $i++;
            }

            $params['last_checked'] = $nowFormatted;
            $types['last_checked'] = \PDO::PARAM_STR;

            $insertSql .= implode(', ', $insertValues);
            $insertSql .= ' ON DUPLICATE KEY UPDATE continent_code = VALUES(continent_code), last_checked = VALUES(last_checked)';

            // Log the SQL query and parameters
            $logger = $this->getEntityManager()->getConnection()->getConfiguration()->getSQLLogger();
            if ($logger) {
                $logger->startQuery($insertSql, $params, $types);
            }

            $result = $connection->executeStatement($insertSql, $params, $types);

            // Log the result
            if ($logger) {
                $logger->stopQuery();
            }

            // Commit the transaction
            $connection->commit();

            // Ensure the entity manager is cleared to avoid caching issues
            $this->getEntityManager()->clear();

            return $result;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Log the error
            $logger = $this->getEntityManager()->getConnection()->getConfiguration()->getSQLLogger();
            if ($logger && method_exists($logger, 'logException')) {
                $logger->logException($e);
            }

            throw $e;
        }
    }
}
