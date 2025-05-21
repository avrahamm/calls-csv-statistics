<?php

namespace App\Service;

use App\Repository\CallRepository;
use App\Repository\ContinentPhonePrefixRepository;
use Psr\Log\LoggerInterface;

class CallsDestContinentEnricher
{
    private CallRepository $callRepository;
    private ContinentPhonePrefixRepository $continentPhonePrefixRepository;
    private LoggerInterface $logger;

    public function __construct(
        CallRepository $callRepository,
        ContinentPhonePrefixRepository $continentPhonePrefixRepository,
        LoggerInterface $logger
    ) {
        $this->callRepository = $callRepository;
        $this->continentPhonePrefixRepository = $continentPhonePrefixRepository;
        $this->logger = $logger;
    }

    /**
     * Enrich dest_continent for calls with empty dest_continent values
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @param array $uniquePhones Array of unique phone numbers to process
     * @return int Number of updated calls
     */
    public function enrichDestContinent(int $uploadedFileId, array $uniquePhones): int
    {
        $this->logger->info('Enriching dest_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'unique_phones_count' => count($uniquePhones)
        ]);

        $totalUpdated = 0;

        // Process phones in batches of 10
        $phoneBatches = array_chunk($uniquePhones, 10);

        foreach ($phoneBatches as $batch) {
            // Get continent codes for the batch of phones
            $phoneToContinent = $this->continentPhonePrefixRepository->findContinentCodesByPhoneNumbersBulk($batch);

            // Filter out null continent codes
            $phoneToContinent = array_filter($phoneToContinent, function ($continent) {
                return $continent !== null;
            });

            if (!empty($phoneToContinent)) {
                // Update dest_continent for calls with matching dialed_number in bulk
                $updated = $this->callRepository->updateDestContinentInBulk($phoneToContinent);
                $totalUpdated += $updated;

                $this->logger->debug('Updated dest_continent for batch', [
                    'batch_size' => count($batch),
                    'updated_calls' => $updated
                ]);
            }
        }

        $this->logger->info('Completed enriching dest_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'total_updated_calls' => $totalUpdated
        ]);

        return $totalUpdated;
    }
}