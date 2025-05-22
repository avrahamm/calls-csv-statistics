<?php

namespace App\MessageHandler;

use App\Message\CalculateCustomerCallStatisticsMessage;
use App\Repository\CallRepository;
use App\Repository\CustomerCallStatisticRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class CalculateCustomerCallStatisticsMessageHandler
{
    private CallRepository $callRepository;
    private CustomerCallStatisticRepository $customerCallStatisticRepository;
    private LoggerInterface $logger;

    public function __construct(
        CallRepository $callRepository,
        CustomerCallStatisticRepository $customerCallStatisticRepository,
        LoggerInterface $logger
    ) {
        $this->callRepository = $callRepository;
        $this->customerCallStatisticRepository = $customerCallStatisticRepository;
        $this->logger = $logger;
    }

    public function __invoke(CalculateCustomerCallStatisticsMessage $message)
    {
        $uploadedFileId = $message->getUploadedFileId();

        $this->logger->info('Starting to calculate customer call statistics', [
            'uploaded_file_id' => $uploadedFileId
        ]);

        try {
            // Get call statistics for the uploaded file
            $statistics = $this->callRepository->getCallStatisticsByUploadedFileId($uploadedFileId);

            if (empty($statistics)) {
                $this->logger->info('No calls found for statistics calculation', [
                    'uploaded_file_id' => $uploadedFileId
                ]);
                return;
            }

            // Update customer call statistics with the delta values
            $updatedCount = $this->customerCallStatisticRepository->updateStatistics($statistics);

            $this->logger->info('Successfully updated customer call statistics', [
                'uploaded_file_id' => $uploadedFileId,
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error calculating customer call statistics', [
                'uploaded_file_id' => $uploadedFileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}