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

    /**
     * Fetch unique dialed_numbers (phones) from the calls table based on uploadedFileId and start/end indexes
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @param int $start The start index
     * @param int $limit The number of records to fetch
     * @return array Array of unique dialed_numbers
     */
    public function findUniquePhonesByUploadedFileId(int $uploadedFileId, int $start, int $limit): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DISTINCT dialed_number
            FROM calls
            WHERE uploaded_file_id = :uploadedFileId
            AND dest_continent IS NULL
            LIMIT :start, :limit
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('uploadedFileId', $uploadedFileId, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        $phones = [];
        while ($row = $result->fetchAssociative()) {
            $phones[] = $row['dialed_number'];
        }

        return $phones;
    }

    /**
     * Count total unique dialed_numbers (phones) from the calls table based on uploadedFileId
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @return int Total number of unique dialed_numbers
     */
    public function countUniquePhonesByUploadedFileId(int $uploadedFileId): int
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(DISTINCT dialed_number) as count
            FROM calls
            WHERE uploaded_file_id = :uploadedFileId
            AND dest_continent IS NULL
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('uploadedFileId', $uploadedFileId, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        return (int) $result->fetchOne();
    }

    /**
     * Update dest_continent for calls with matching dialed_number in bulk
     * 
     * This method takes a dictionary of phone numbers mapped to continent codes
     * and updates all matching Call entities where dest_continent is NULL.
     * 
     * @param array $phoneToContinent Associative array of phone numbers and their continent codes
     * @return int Number of updated records
     */
    public function updateDestContinentInBulk(array $phoneToContinent): int
    {
        if (empty($phoneToContinent)) {
            return 0;
        }

        $connection = $this->getEntityManager()->getConnection();

        try {
            // Begin transaction
            $connection->beginTransaction();

            // Create a temporary table with explicit collation to match the "calls" table
            $connection->executeStatement('
                CREATE TEMPORARY TABLE tmp_phone_continent (
                    phone VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY,
                    continent VARCHAR(2)
                )
            ');

            // Insert phone-to-continent mappings into the temporary table
            $insertSql = 'INSERT INTO tmp_phone_continent VALUES ';
            $insertValues = [];
            $params = [];
            $types = [];

            foreach ($phoneToContinent as $phone => $continent) {
                $phoneParam = 'phone_' . md5($phone);
                $continentParam = 'continent_' . md5($phone);

                $insertValues[] = '(:' . $phoneParam . ', :' . $continentParam . ')';
                $params[$phoneParam] = $phone;
                $params[$continentParam] = $continent;
                $types[$phoneParam] = \PDO::PARAM_STR;
                $types[$continentParam] = \PDO::PARAM_STR;
            }

            $insertSql .= implode(', ', $insertValues);
            $connection->executeStatement($insertSql, $params, $types);

            // Update calls table
            $updateSql = '
                UPDATE calls c
                JOIN tmp_phone_continent t ON c.dialed_number = t.phone
                SET c.dest_continent = t.continent
                WHERE c.dest_continent IS NULL
            ';

            $result = $connection->executeStatement($updateSql);

            // Drop the temporary table
            $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_phone_continent');

            // Commit the transaction
            $connection->commit();

            return $result;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Ensure the temporary table is dropped even if an error occurs
            try {
                $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_phone_continent');
            } catch (\Exception $dropException) {
                // Ignore errors when dropping the table
            }

            throw $e;
        }
    }

    /**
     * Fetch unique source_ip from the calls table based on uploadedFileId and start/end indexes
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @param int $start The start index
     * @param int $limit The number of records to fetch
     * @return array Array of unique source_ip
     */
    public function findUniqueSourceIpsByUploadedFileId(int $uploadedFileId, int $start, int $limit): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DISTINCT source_ip
            FROM calls
            WHERE uploaded_file_id = :uploadedFileId
            AND source_continent IS NULL
            LIMIT :start, :limit
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('uploadedFileId', $uploadedFileId, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        $ips = [];
        while ($row = $result->fetchAssociative()) {
            $ips[] = $row['source_ip'];
        }

        return $ips;
    }

    /**
     * Update source_continent for calls with matching source_ip in bulk
     * 
     * This method takes a dictionary of IP addresses mapped to continent codes
     * and updates all matching Call entities where source_continent is NULL.
     * 
     * @param array $ipToContinent Associative array of IP addresses and their continent codes
     * @return int Number of updated records
     */
    public function updateSourceContinentInBulk(array $ipToContinent): int
    {
        if (empty($ipToContinent)) {
            return 0;
        }

        $connection = $this->getEntityManager()->getConnection();

        try {
            // Begin transaction
            $connection->beginTransaction();

            // Create a temporary table with explicit collation to match the "calls" table
            $connection->executeStatement('
                CREATE TEMPORARY TABLE tmp_ip_continent (
                    ip VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY,
                    continent VARCHAR(2)
                )
            ');

            // Insert ip-to-continent mappings into the temporary table
            $insertSql = 'INSERT INTO tmp_ip_continent VALUES ';
            $insertValues = [];
            $params = [];
            $types = [];

            foreach ($ipToContinent as $ip => $continent) {
                $ipParam = 'ip_' . md5($ip);
                $continentParam = 'continent_' . md5($ip);

                $insertValues[] = '(:' . $ipParam . ', :' . $continentParam . ')';
                $params[$ipParam] = $ip;
                $params[$continentParam] = $continent;
                $types[$ipParam] = \PDO::PARAM_STR;
                $types[$continentParam] = \PDO::PARAM_STR;
            }

            $insertSql .= implode(', ', $insertValues);
            $connection->executeStatement($insertSql, $params, $types);

            // Update calls table
            $updateSql = '
                UPDATE calls c
                JOIN tmp_ip_continent t ON c.source_ip = t.ip
                SET c.source_continent = t.continent
                WHERE c.source_continent IS NULL
            ';

            $result = $connection->executeStatement($updateSql);

            // Drop the temporary table
            $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_ip_continent');

            // Commit the transaction
            $connection->commit();

            return $result;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Ensure the temporary table is dropped even if an error occurs
            try {
                $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_ip_continent');
            } catch (\Exception $dropException) {
                // Ignore errors when dropping the table
            }

            throw $e;
        }
    }

    /**
     * Update within_same_cont field for all calls in a file where both source_continent and dest_continent are set
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @return int Number of updated records
     */
    public function updateWithinSameContinent(int $uploadedFileId): int
    {
        $connection = $this->getEntityManager()->getConnection();

        try {
            // Begin transaction
            $connection->beginTransaction();

            // Update calls table - set within_same_cont to true if source_continent equals dest_continent
            $updateSql = '
                UPDATE calls
                SET within_same_cont = (source_continent = dest_continent)
                WHERE uploaded_file_id = :uploadedFileId
                AND source_continent IS NOT NULL
                AND dest_continent IS NOT NULL
                AND within_same_cont IS NULL
            ';

            $stmt = $connection->prepare($updateSql);
            $stmt->bindValue('uploadedFileId', $uploadedFileId, \PDO::PARAM_INT);
            $result = $stmt->executeStatement();

            // Commit the transaction
            $connection->commit();

            return $result;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }
}
