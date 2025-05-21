<?php

namespace App\Service;

use App\Entity\Call;
use App\Entity\UploadedFile;
use App\Repository\CallRepository;
use App\Repository\UploadedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CallsCsvProcessor
{
    private CallRepository $callRepository;
    private UploadedFileRepository $uploadedFileRepository;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        CallRepository $callRepository,
        UploadedFileRepository $uploadedFileRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ) {
        $this->callRepository = $callRepository;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
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

        $this->uploadedFileRepository->save($uploadedFile, true);

        $result = $this->processFileByPath($filePath, $uploadedFile);

        return $result;
    }

    /**
     * Process an UploadedFile entity
     */
    private function processFile(UploadedFile $uploadedFile): bool
    {
        // Update status to processing
        $uploadedFile->setStatus('processing');
        $this->uploadedFileRepository->save($uploadedFile, true);

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

            while (($row = fgetcsv($handle)) !== false) {
                $call = $this->createCallFromCsvRow($row);
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

            fclose($handle);

            // Update status to completed
            $uploadedFile->setStatus('completed');
            $uploadedFile->setProcessedAt(new \DateTime());
            $this->uploadedFileRepository->save($uploadedFile, true);

            return true;
        } catch (\Exception $e) {
            // Update status to "failed"
            $uploadedFile->setStatus('failed');
            $uploadedFile->setErrorMessage($e->getMessage());
            $this->uploadedFileRepository->save($uploadedFile, true);

            return false;
        }
    }

    /**
     * Create a Call entity from a CSV row
     */
    private function createCallFromCsvRow(array $row): Call
    {
        $call = new Call();
        $call->setCustomerId((int)$row[0]);
        $call->setCallDate(new \DateTime($row[1]));
        $call->setDuration((int)$row[2]);
        $call->setDialedNumber($row[3]);
        $call->setSourceIp($row[4]);

        return $call;
    }

    /**
     * Process a batch of Call entities
     */
    private function processBatch(array $batch): void
    {
        foreach ($batch as $call) {
            $this->entityManager->persist($call);
        }

        $this->entityManager->flush();
    }
}
