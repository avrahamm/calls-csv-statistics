<?php

namespace App\Repository;

use App\Entity\CustomerCallStatistic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerCallStatistic>
 *
 * @method CustomerCallStatistic|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerCallStatistic|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerCallStatistic[]    findAll()
 * @method CustomerCallStatistic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerCallStatisticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerCallStatistic::class);
    }

    public function save(CustomerCallStatistic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomerCallStatistic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find statistics that have been updated since a given date for specific customer IDs
     * and return a list of all current customer IDs
     *
     * @param array $customerUpdates Array of [customer_id => last_updated] pairs
     * @return array [updatedStatistics, allCustomerIds]
     * @throws \Exception
     */
    public function findUpdatedStatistics(array $customerUpdates): array
    {
        // Get all current customer IDs from the database
        $allStatistics = $this->findAll();
        $allCustomerIds = [];
        foreach ($allStatistics as $statistic) {
            $allCustomerIds[] = $statistic->getCustomerId();
        }

        if (empty($customerUpdates)) {
            return ['updatedStatistics' => [], 'allCustomerIds' => $allCustomerIds];
        }

        $updatedStatistics = [];

        foreach ($customerUpdates as $customerId => $lastUpdated) {
            $lastUpdatedDate = new \DateTime($lastUpdated);
            $statistic = $this->find($customerId);

            if ($statistic && $statistic->getLastUpdated() > $lastUpdatedDate) {
                $updatedStatistics[] = $statistic;
            }
        }

        // Find new customer IDs that weren't in the client's list
        $newStatistics = [];
        foreach ($allStatistics as $statistic) {
            $customerId = $statistic->getCustomerId();
            if (!isset($customerUpdates[$customerId])) {
                $newStatistics[] = $statistic;
            }
        }

        // Combine updated and new statistics
        $updatedStatistics = array_merge($updatedStatistics, $newStatistics);

        return [
            'updatedStatistics' => $updatedStatistics,
            'allCustomerIds' => $allCustomerIds
        ];
    }

    /**
     * Update customer call statistics with delta values from an uploaded file
     *
     * @param array $statistics Array of statistics grouped by customer_id
     * @return int Number of updated/inserted records
     * @throws Exception
     */
    public function updateStatistics(array $statistics): int
    {
        if (empty($statistics)) {
            return 0;
        }

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $count = 0;

        try {
            // Begin transaction
            $connection->beginTransaction();

            foreach ($statistics as $stat) {
                $customerId = $stat['customer_id'];
                $entity = $this->find($customerId);

                if (!$entity) {
                    // Create a new entity if it doesn't exist
                    $entity = new \App\Entity\CustomerCallStatistic();
                    $entity->setCustomerId($customerId);
                    $entity->setNumCallsWithinSameContinent($stat['num_calls_within_same_continent']);
                    $entity->setTotalDurationWithinSameCont($stat['total_duration_within_same_cont']);
                    $entity->setTotalNumCalls($stat['total_num_calls']);
                    $entity->setTotalCallsDuration($stat['total_calls_duration']);
                } else {
                    // Update an existing entity with delta values
                    $entity->setNumCallsWithinSameContinent(
                        $entity->getNumCallsWithinSameContinent() + $stat['num_calls_within_same_continent']
                    );
                    $entity->setTotalDurationWithinSameCont(
                        $entity->getTotalDurationWithinSameCont() + $stat['total_duration_within_same_cont']
                    );
                    $entity->setTotalNumCalls(
                        $entity->getTotalNumCalls() + $stat['total_num_calls']
                    );
                    $entity->setTotalCallsDuration(
                        $entity->getTotalCallsDuration() + $stat['total_calls_duration']
                    );
                }

                $entity->setLastUpdated(new \DateTime());
                $em->persist($entity);
                $count++;
            }

            $em->flush();
            $connection->commit();

            return $count;
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }
}
