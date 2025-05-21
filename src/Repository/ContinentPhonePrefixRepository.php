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
}
