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

    /**
     * Find continent code by phone number
     * 
     * For each phone, first take 6 characters, then 5 characters and so on till 1 character.
     * Check if each segment equals some phone extension.
     * If yes, that's the answer and return it.
     * If not found, return NULL.
     * 
     * @param string $phoneNumber The phone number to check
     * @return string|null The continent code if found, null otherwise
     */
    public function findContinentCodeByPhoneNumber(string $phoneNumber): ?string
    {
        // Start with the maximum prefix length (6) and decrease to 1
        for ($length = 6; $length >= 1; $length--) {
            // Extract the prefix of the current length
            $prefix = substr($phoneNumber, 0, $length);

            // Find the continent phone prefix entity
            $continentPhonePrefix = $this->find($prefix);

            // If found, return the continent code
            if ($continentPhonePrefix) {
                return $continentPhonePrefix->getContinentCode();
            }
        }

        // If no match found, return null
        return null;
    }

    /**
     * Find continent codes for multiple phone numbers
     * 
     * Process an array of phone numbers and return an associative array
     * with phone numbers as keys and continent codes as values.
     * 
     * @param array $phoneNumbers Array of phone numbers to check
     * @return array Associative array of phone numbers and their continent codes
     */
    public function findContinentCodesByPhoneNumbers(array $phoneNumbers): array
    {
        $result = [];

        foreach ($phoneNumbers as $phoneNumber) {
            $result[$phoneNumber] = $this->findContinentCodeByPhoneNumber($phoneNumber);
        }

        return $result;
    }

    /**
     * Find continent codes for multiple phone numbers using a bulk query
     * 
     * Uses a temporary table approach to efficiently process multiple phone numbers at once.
     * This is more efficient than processing each phone number individually.
     * 
     * @param array $phoneNumbers Array of phone numbers to check
     * @return array Associative array of phone numbers and their continent codes
     */
    public function findContinentCodesByPhoneNumbersBulk(array $phoneNumbers): array
    {
        if (empty($phoneNumbers)) {
            return [];
        }

        $connection = $this->getEntityManager()->getConnection();
        $result = [];

        try {
            // Create a temporary table with explicit collation to match the continent_phone_prefix table
            $connection->executeStatement('
                CREATE TEMPORARY TABLE tmp_phones (
                    phone_prefix VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY
                )
            ');

            // Insert phone numbers into a temporary table
            $insertSql = 'INSERT INTO tmp_phones VALUES ';
            $insertValues = [];
            $params = [];
            $types = [];

            foreach ($phoneNumbers as $index => $phoneNumber) {
                $paramName = 'phone_' . $index;
                $insertValues[] = '(:' . $paramName . ')';
                $params[$paramName] = $phoneNumber;
                $types[$paramName] = \PDO::PARAM_STR;

                // Initialize a result with null values
                $result[$phoneNumber] = null;
            }

            $insertSql .= implode(', ', $insertValues);
            $connection->executeStatement($insertSql, $params, $types);

            // For each prefix length (6 down to 1), find matching prefixes
            for ($length = 6; $length >= 1; $length--) {
                // Find matches for the current prefix length
                $sql = '
                    SELECT t.phone_prefix, c.continent_code
                    FROM tmp_phones AS t
                    JOIN continent_phone_prefix AS c
                    ON LEFT(t.phone_prefix, :length) = c.phone_prefix
                    WHERE LENGTH(c.phone_prefix) = :length
                ';

                $stmt = $connection->executeQuery($sql, ['length' => $length]);

                // Process results
                while ($row = $stmt->fetchAssociative()) {
                    $phoneNumber = $row['phone_prefix'];
                    // Only set the result if it hasn't been set by a longer prefix
                    if ($result[$phoneNumber] === null) {
                        $result[$phoneNumber] = $row['continent_code'];
                    }
                }
            }

            // Drop a temporary table
            $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_phones');
        } catch (\Exception $e) {
            // Ensure a temporary table is dropped even if an error occurs
            try {
                $connection->executeStatement('DROP TEMPORARY TABLE IF EXISTS tmp_phones');
            } catch (\Exception $dropException) {
                // Ignore errors when dropping the table
            }

            // Fallback to non-bulk method
            return $this->findContinentCodesByPhoneNumbers($phoneNumbers);
        }

        return $result;
    }
}
