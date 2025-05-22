<?php

namespace App\MessageHandler;

use App\Message\EnrichWithinSameContMessage;
use App\Message\CalculateCustomerCallStatisticsMessage;
use App\Repository\CallRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class EnrichWithinSameContMessageHandler
{
    private CallRepository $callRepository;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(
        CallRepository $callRepository,
        LoggerInterface $logger,
        MessageBusInterface $messageBus
    ) {
        $this->callRepository = $callRepository;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
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

            // Dispatch message to calculate customer call statistics
            $this->messageBus->dispatch(new CalculateCustomerCallStatisticsMessage($uploadedFileId));

            $this->logger->info('Dispatched CalculateCustomerCallStatisticsMessage', [
                'uploaded_file_id' => $uploadedFileId
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
