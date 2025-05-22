<?php

namespace App\MessageHandler;

use App\Message\EnrichWithinSameContMessage;
use App\Repository\CallRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class EnrichWithinSameContMessageHandler
{
    private CallRepository $callRepository;
    private LoggerInterface $logger;

    public function __construct(
        CallRepository $callRepository,
        LoggerInterface $logger
    ) {
        $this->callRepository = $callRepository;
        $this->logger = $logger;
    }

    public function __invoke(EnrichWithinSameContMessage $message)
    {
        $uploadedFileId = $message->getUploadedFileId();

        $this->logger->info('Starting to update within_same_cont for calls', [
            'uploaded_file_id' => $uploadedFileId
        ]);

        try {
            // Update within_same_cont for all calls in the file
            $updatedCount = $this->callRepository->updateWithinSameContinent($uploadedFileId);

            $this->logger->info('Successfully updated within_same_cont for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating within_same_cont for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}