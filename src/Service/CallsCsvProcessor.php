<?php

namespace App\Service;

use App\Entity\Call;
use App\Entity\UploadedFile;
use App\Message\EnrichDestContinentMessage;
use App\Message\EnrichSourceContinentMessage;
use App\Repository\CallRepository;
use App\Repository\ContinentPhonePrefixRepository;
use App\Repository\UploadedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CallsCsvProcessor
{
    private CallRepository $callRepository;
    private UploadedFileRepository $uploadedFileRepository;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private ContinentPhonePrefixRepository $continentPhonePrefixRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        CallRepository $callRepository,
        UploadedFileRepository $uploadedFileRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        ContinentPhonePrefixRepository $continentPhonePrefixRepository,
        MessageBusInterface $messageBus
    ) {
        $this->callRepository = $callRepository;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->continentPhonePrefixRepository = $continentPhonePrefixRepository;
        $this->messageBus = $messageBus;
    }

    /**
     * Process a CSV file by its ID in the uploaded_files table
     */
    public function processUploadedFile(int $fileId): bool
    {
        $uploadedFile = $this->uploadedFileRepository->find($fileId);

        if (!$uploadedFile) {
            return false;
        }

        return $this->processFile($uploadedFile);
    }

    /**
     * Process a CSV file by its path
     */
    public function processFilePath(string $filePath): bool
    {
        // Create a temporary UploadedFile entity for tracking
        $uploadedFile = new UploadedFile();
        $uploadedFile->setFileName(basename($filePath));
        $uploadedFile->setUploadedAt(new \DateTime());
        $uploadedFile->setStatus('processing');

        // Save the UploadedFile entity
        try {
            $this->uploadedFileRepository->save($uploadedFile, true);
        } catch (\Exception $e) {
            // If we can't save the entity, we can't process the file
            return false;
        }

        $result = $this->processFileByPath($filePath, $uploadedFile);

        return $result;
    }

    /**
     * Process an UploadedFile entity
     */
    private function processFile(UploadedFile $uploadedFile): bool
    {
        // Update the status to processing
        try {
            $uploadedFile->setStatus('processing');
            $this->uploadedFileRepository->save($uploadedFile, true);
        } catch (\Exception $e) {
            // If we can't update the status, we can't process the file
            return false;
        }

        $uploadPath = $this->parameterBag->get('kernel.project_dir') . '/' . $this->parameterBag->get('upload_path');
        $filePath = $uploadPath . '/' . $uploadedFile->getFileName();

        return $this->processFileByPath($filePath, $uploadedFile);
    }

    /**
     * Process a file by its path and update the UploadedFile entity
     */
    private function processFileByPath(string $filePath, UploadedFile $uploadedFile): bool
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: $filePath");
            }

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Could not open file: $filePath");
            }

            // Process rows in batches of 10
            $batch = [];
            $rowCount = 0;

            // Begin transaction for Call table operations
            $this->entityManager->beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $call = $this->createCallFromCsvRow($row, $uploadedFile->getId());
                $batch[] = $call;
                $rowCount++;

                // Process the batch when it reaches size 10
                if (count($batch) >= 10) {
                    $this->processBatch($batch);
                    $batch = [];
                }
            }

            // Process any remaining rows
            if (!empty($batch)) {
                $this->processBatch($batch);
            }

            // Commit transaction for Call table operations
            $this->entityManager->commit();

            fclose($handle);

            // Update status to completed (no transaction needed)
            $uploadedFile->setStatus('completed');
            $uploadedFile->setProcessedAt(new \DateTime());
            $this->uploadedFileRepository->save($uploadedFile, true);

            // Dispatch a message to enrich dest_continent for calls with empty dest_continent
            // Use the new approach with start and end indexes
            $destOffset = $this->parameterBag->get('enrich_dest_continent_offset');
            $destMessage = new EnrichDestContinentMessage($uploadedFile->getId(), 0, $destOffset, $destOffset);
            $this->messageBus->dispatch($destMessage);

            // Dispatch a message to enrich source_continent for calls with empty source_continent
            $sourceOffset = $this->parameterBag->get('enrich_source_continent_offset');
            $sourceMessage = new EnrichSourceContinentMessage($uploadedFile->getId(), 0, $sourceOffset, $sourceOffset);
            $this->messageBus->dispatch($sourceMessage);

            return true;
        } catch (\Exception $e) {
            // Rollback transaction if an error occurred and it's active
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }

            // Update the status to "failed" (no transaction needed)
            try {
                $uploadedFile->setStatus('failed');
                $uploadedFile->setErrorMessage($e->getMessage());
                $this->uploadedFileRepository->save($uploadedFile, true);
            } catch (\Exception $statusUpdateException) {
                // We could log this error, but for now we'll just continue
            }

            return false;
        }
    }

    /**
     * Create a Call entity from a CSV row
     */
    private function createCallFromCsvRow(array $row, ?int $uploadedFileId = null): Call
    {
        $call = new Call();
        $call->setCustomerId((int)$row[0]);
        $call->setCallDate(new \DateTime($row[1]));
        $call->setDuration((int)$row[2]);
        $call->setDialedNumber($row[3]);
        $call->setSourceIp($row[4]);
        $call->setUploadedFileId($uploadedFileId);

        // We'll set dest_continent in bulk later for better performance
        // For now, leave it as null

        // TODO: Determine the source continent based on IP address (not part of this task)
        // For now, we'll leave source_continent as null

        // Set within_same_cont if both source and destination continents are known
//        if ($call->getSourceContinent() !== null && $call->getDestContinent() !== null) {
//            $call->setWithinSameCont($call->getSourceContinent() === $call->getDestContinent());
//        }

        return $call;
    }

    /**
     * Process a batch of Call entities
     */
    private function processBatch(array $batch): void
    {
        // First, persist all calls
        foreach ($batch as $call) {
            $this->entityManager->persist($call);
        }
        $this->entityManager->flush();

        // Collect all unique dialed numbers from the batch
        $dialedNumbers = array_unique(array_map(function (Call $call) {
            return $call->getDialedNumber();
        }, $batch));

        if (!empty($dialedNumbers)) {
            // Get continent codes for all dialed numbers in bulk
            $phoneToContinent = $this->continentPhonePrefixRepository->findContinentCodesByPhoneNumbersBulk($dialedNumbers);

            // Filter out null continent codes
            $phoneToContinent = array_filter($phoneToContinent, function ($continent) {
                return $continent !== null;
            });

            if (!empty($phoneToContinent)) {
                // Update dest_continent for all calls in bulk
                $this->callRepository->updateDestContinentInBulk($phoneToContinent);

                // Update within_same_cont for calls in the current batch
                $this->updateWithinSameContForBatch($batch, $phoneToContinent);
            }
        }
    }

    /**
     * Update within_same_cont for a batch of calls
     */
    private function updateWithinSameContForBatch(array $batch, array $phoneToContinent): void
    {
        foreach ($batch as $call) {
            $dialedNumber = $call->getDialedNumber();
            $destContinent = $phoneToContinent[$dialedNumber] ?? null;

            // If we have both source and destination continents, update within_same_cont
            if ($destContinent !== null && $call->getSourceContinent() !== null) {
                $call->setWithinSameCont($call->getSourceContinent() === $destContinent);
                $this->entityManager->persist($call);
            }
        }

        $this->entityManager->flush();
    }
}
