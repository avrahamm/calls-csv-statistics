<?php

namespace App\MessageHandler;

use App\Message\EnrichDestContinentMessage;
use App\Service\CallsDestContinentEnricher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class EnrichDestContinentMessageHandler
{
    private CallsDestContinentEnricher $callsDestContinentEnricher;
    private LoggerInterface $logger;

    public function __construct(
        CallsDestContinentEnricher $callsDestContinentEnricher,
        LoggerInterface $logger
    ) {
        $this->callsDestContinentEnricher = $callsDestContinentEnricher;
        $this->logger = $logger;
    }

    public function __invoke(EnrichDestContinentMessage $message)
    {
        $uploadedFileId = $message->getUploadedFileId();
        $uniquePhones = $message->getUniquePhones();
        
        $this->logger->info('Starting to enrich dest_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'unique_phones_count' => count($uniquePhones)
        ]);
        
        try {
            $updatedCount = $this->callsDestContinentEnricher->enrichDestContinent($uploadedFileId, $uniquePhones);
            
            $this->logger->info('Successfully enriched dest_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error enriching dest_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}