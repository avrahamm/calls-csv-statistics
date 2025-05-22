<?php

namespace App\MessageHandler;

use App\Message\EnrichDestContinentMessage;
use App\Message\EnrichSourceContinentMessage;
use App\Message\FinalizeCallsImportMessage;
use App\Repository\CallStagingRepository;
use App\Repository\UploadedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class FinalizeCallsImportMessageHandler
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private UploadedFileRepository $uploadedFileRepository;
    private CallStagingRepository $callStagingRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UploadedFileRepository $uploadedFileRepository,
        CallStagingRepository $callStagingRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->callStagingRepository = $callStagingRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(FinalizeCallsImportMessage $message)
    {
        $batchId = $message->getBatchId();
        $uploadedFileId = $message->getUploadedFileId();
        $chunkFilenames = $message->getChunkFilenames();

        $this->logger->info("Finalizing import for batch $batchId with " . count($chunkFilenames) . " chunks");

        try {
            // Get the uploaded file entity
            $uploadedFile = $this->uploadedFileRepository->find($uploadedFileId);
            if (!$uploadedFile) {
                throw new \Exception("Uploaded file with ID $uploadedFileId not found");
            }

            // Check if there are any invalid records in the staging table
            $hasInvalidRecords = $this->callStagingRepository->hasInvalidRecords($batchId);

            if ($hasInvalidRecords) {
                $this->logger->error("Invalid records found in batch $batchId. Import will be rolled back.");

                // Update the uploaded file status to failed
                $uploadedFile->setStatus('failed');
                $uploadedFile->setErrorMessage("Invalid records found in the CSV file");
                $this->uploadedFileRepository->save($uploadedFile, true);

                // Clean up the staging table
                $this->callStagingRepository->deleteByBatchId($batchId);

                // Clean up chunk files
                $this->cleanupChunkFiles($chunkFilenames);

                return false;
            }

            // Begin transaction for the final commit
            $this->entityManager->beginTransaction();

            try {
                // Transfer data from staging to final table
                $rowsTransferred = $this->callStagingRepository->transferToCalls($batchId);

                $this->logger->info("Transferred $rowsTransferred rows from staging to final table for batch $batchId");

                // Update the uploaded file status to completed
                $uploadedFile->setStatus('completed');
                $uploadedFile->setProcessedAt(new \DateTime());
                $this->uploadedFileRepository->save($uploadedFile, true);

                // Commit the transaction
                $this->entityManager->commit();

                // Clean up the staging table
                $this->callStagingRepository->deleteByBatchId($batchId);

                // Clean up chunk files
                $this->cleanupChunkFiles($chunkFilenames);

                // Dispatch messages to enrich destination and source continents
                $this->dispatchEnrichmentMessages($uploadedFileId);

                return true;
            } catch (\Exception $e) {
                // Rollback the transaction if an error occurred
                if ($this->entityManager->getConnection()->isTransactionActive()) {
                    $this->entityManager->rollback();
                }

                $this->logger->error("Error transferring data from staging to final table: " . $e->getMessage());

                // Update the uploaded file status to failed
                $uploadedFile->setStatus('failed');
                $uploadedFile->setErrorMessage($e->getMessage());
                $this->uploadedFileRepository->save($uploadedFile, true);

                // Clean up the staging table
                $this->callStagingRepository->deleteByBatchId($batchId);

                // Clean up chunk files
                $this->cleanupChunkFiles($chunkFilenames);

                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Error finalizing import: " . $e->getMessage());

            // Try to update the uploaded file status if possible
            try {
                $uploadedFile = $this->uploadedFileRepository->find($uploadedFileId);
                if ($uploadedFile) {
                    $uploadedFile->setStatus('failed');
                    $uploadedFile->setErrorMessage($e->getMessage());
                    $this->uploadedFileRepository->save($uploadedFile, true);
                }
            } catch (\Exception $statusUpdateException) {
                $this->logger->error("Error updating uploaded file status: " . $statusUpdateException->getMessage());
            }

            // Try to clean up the staging table
            try {
                $this->callStagingRepository->deleteByBatchId($batchId);
            } catch (\Exception $cleanupException) {
                $this->logger->error("Error cleaning up staging table: " . $cleanupException->getMessage());
            }

            // Try to clean up chunk files
            try {
                $this->cleanupChunkFiles($chunkFilenames);
            } catch (\Exception $cleanupException) {
                $this->logger->error("Error cleaning up chunk files: " . $cleanupException->getMessage());
            }

            return false;
        }
    }

    /**
     * Clean up chunk files and empty the chunks directory
     */
    private function cleanupChunkFiles(array $chunkFilenames): void
    {
        $chunksPath = $this->parameterBag->get('kernel.project_dir') . '/' . $this->parameterBag->get('chunks_path');

        // First, delete the specific chunk files that were processed
        foreach ($chunkFilenames as $chunkFilename) {
            $chunkFilePath = $chunksPath . '/' . $chunkFilename;
            if (file_exists($chunkFilePath)) {
                unlink($chunkFilePath);
            }
        }

        // Then, clean up any remaining files in the chunks directory
        $remainingFiles = glob($chunksPath . '/*');
        foreach ($remainingFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->logger->info("Cleaned up chunks directory: $chunksPath");
    }

    /**
     * Dispatch messages to enrich destination and source continents
     */
    private function dispatchEnrichmentMessages(int $uploadedFileId): void
    {
        // Dispatch a message to enrich dest_continent for calls with empty dest_continent
        $destOffset = $this->parameterBag->get('enrich_dest_continent_offset');
        $destMessage = new EnrichDestContinentMessage($uploadedFileId, 0, $destOffset, $destOffset);
        $this->messageBus->dispatch($destMessage);

        // Dispatch a message to enrich source_continent for calls with empty source_continent
        $sourceOffset = $this->parameterBag->get('enrich_source_continent_offset');
        $sourceMessage = new EnrichSourceContinentMessage($uploadedFileId, 0, $sourceOffset, $sourceOffset);
        $this->messageBus->dispatch($sourceMessage);
    }
}
