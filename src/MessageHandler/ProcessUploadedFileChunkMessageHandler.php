<?php

namespace App\MessageHandler;

use App\Entity\CallStaging;
use App\Message\ProcessUploadedFileChunkMessage;
use App\Repository\CallStagingRepository;
use App\Repository\UploadedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class ProcessUploadedFileChunkMessageHandler
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private UploadedFileRepository $uploadedFileRepository;
    private CallStagingRepository $callStagingRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UploadedFileRepository $uploadedFileRepository,
        CallStagingRepository $callStagingRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->callStagingRepository = $callStagingRepository;
        $this->logger = $logger;
    }

    public function __invoke(ProcessUploadedFileChunkMessage $message)
    {
        $chunkFilename = $message->getChunkFilename();
        $batchId = $message->getBatchId();
        $uploadedFileId = $message->getUploadedFileId();

        $chunksPath = $this->parameterBag->get('kernel.project_dir') . '/' . $this->parameterBag->get('chunks_path');
        $chunkFilePath = $chunksPath . '/' . $chunkFilename;

        try {
            if (!file_exists($chunkFilePath)) {
                throw new \Exception("Chunk file not found: $chunkFilePath");
            }

            $handle = fopen($chunkFilePath, 'r');
            if (!$handle) {
                throw new \Exception("Could not open chunk file: $chunkFilePath");
            }

            // Process rows in batches
            $batch = [];
            $rowNumber = 0;

            // Begin transaction for staging table operations
            $this->entityManager->beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                try {
                    // Validate the row
                    $this->validateRow($row);
                    
                    // Create a CallStaging entity from the row
                    $callStaging = $this->createCallStagingFromCsvRow($row, $uploadedFileId, $batchId, $chunkFilename, $rowNumber);
                    $batch[] = $callStaging;
                    
                    // Process the batch when it reaches size 10
                    if (count($batch) >= 10) {
                        $this->processBatch($batch);
                        $batch = [];
                    }
                } catch (\Exception $e) {
                    // Log the error
                    $this->logger->error("Error processing row $rowNumber in chunk $chunkFilename: " . $e->getMessage());
                    
                    // Create an invalid CallStaging entity with error message
                    $callStaging = $this->createInvalidCallStagingFromCsvRow($row, $uploadedFileId, $batchId, $chunkFilename, $rowNumber, $e->getMessage());
                    $this->callStagingRepository->save($callStaging, true);
                    
                    // Continue processing other rows
                }
            }

            // Process any remaining rows
            if (!empty($batch)) {
                $this->processBatch($batch);
            }

            // Commit transaction for staging table operations
            $this->entityManager->commit();

            fclose($handle);

            return true;
        } catch (\Exception $e) {
            // Rollback transaction if an error occurred and it's active
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }

            $this->logger->error("Error processing chunk $chunkFilename: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Validate a CSV row
     * 
     * @throws \Exception if validation fails
     */
    private function validateRow(array $row): void
    {
        // Check if the row has the expected number of columns
        if (count($row) < 5) {
            throw new \Exception("Invalid row format: expected at least 5 columns, got " . count($row));
        }

        // Validate customer_id (column 0)
        if (!is_numeric($row[0]) || (int)$row[0] <= 0) {
            throw new \Exception("Invalid customer_id: must be a positive integer");
        }

        // Validate call_date (column 1)
        try {
            new \DateTime($row[1]);
        } catch (\Exception $e) {
            throw new \Exception("Invalid call_date: " . $e->getMessage());
        }

        // Validate duration (column 2)
        if (!is_numeric($row[2]) || (int)$row[2] < 0) {
            throw new \Exception("Invalid duration: must be a non-negative integer");
        }

        // Validate dialed_number (column 3)
        if (empty($row[3]) || strlen($row[3]) > 32) {
            throw new \Exception("Invalid dialed_number: must not be empty and not exceed 32 characters");
        }

        // Validate source_ip (column 4)
        if (empty($row[4]) || strlen($row[4]) > 45 || !filter_var($row[4], FILTER_VALIDATE_IP)) {
            throw new \Exception("Invalid source_ip: must be a valid IP address");
        }
    }

    /**
     * Create a CallStaging entity from a CSV row
     */
    private function createCallStagingFromCsvRow(array $row, int $uploadedFileId, string $batchId, string $chunkFilename, int $rowNumber): CallStaging
    {
        $callStaging = new CallStaging();
        $callStaging->setCustomerId((int)$row[0]);
        $callStaging->setCallDate(new \DateTime($row[1]));
        $callStaging->setDuration((int)$row[2]);
        $callStaging->setDialedNumber($row[3]);
        $callStaging->setSourceIp($row[4]);
        $callStaging->setUploadedFileId($uploadedFileId);
        $callStaging->setBatchId($batchId);
        $callStaging->setChunkFilename($chunkFilename);
        $callStaging->setRowNumberInChunk($rowNumber);
        $callStaging->setIsValid(true);

        return $callStaging;
    }

    /**
     * Create an invalid CallStaging entity from a CSV row
     */
    private function createInvalidCallStagingFromCsvRow(array $row, int $uploadedFileId, string $batchId, string $chunkFilename, int $rowNumber, string $errorMessage): CallStaging
    {
        $callStaging = new CallStaging();
        
        // Set the basic fields that we can
        if (count($row) > 0 && is_numeric($row[0])) {
            $callStaging->setCustomerId((int)$row[0]);
        } else {
            $callStaging->setCustomerId(0); // Default value
        }
        
        try {
            if (count($row) > 1) {
                $callStaging->setCallDate(new \DateTime($row[1]));
            } else {
                $callStaging->setCallDate(new \DateTime());
            }
        } catch (\Exception $e) {
            $callStaging->setCallDate(new \DateTime());
        }
        
        if (count($row) > 2 && is_numeric($row[2])) {
            $callStaging->setDuration((int)$row[2]);
        } else {
            $callStaging->setDuration(0);
        }
        
        if (count($row) > 3) {
            $callStaging->setDialedNumber($row[3]);
        } else {
            $callStaging->setDialedNumber('unknown');
        }
        
        if (count($row) > 4) {
            $callStaging->setSourceIp($row[4]);
        } else {
            $callStaging->setSourceIp('0.0.0.0');
        }
        
        $callStaging->setUploadedFileId($uploadedFileId);
        $callStaging->setBatchId($batchId);
        $callStaging->setChunkFilename($chunkFilename);
        $callStaging->setRowNumberInChunk($rowNumber);
        $callStaging->setIsValid(false);
        $callStaging->setErrorMessage($errorMessage);

        return $callStaging;
    }

    /**
     * Process a batch of CallStaging entities
     */
    private function processBatch(array $batch): void
    {
        foreach ($batch as $callStaging) {
            $this->entityManager->persist($callStaging);
        }
        $this->entityManager->flush();
    }
}