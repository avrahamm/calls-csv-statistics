<?php

namespace App\Service;

use App\Entity\UploadedFile;
use App\Message\FinalizeCallsImportMessage;
use App\Message\ProcessUploadedFileChunkMessage;
use App\Repository\UploadedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ParallelCallsCsvProcessor
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private UploadedFileRepository $uploadedFileRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UploadedFileRepository $uploadedFileRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
        Filesystem $filesystem
    ) {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    /**
     * Process a CSV file by its ID in the uploaded_files table
     */
    public function processUploadedFile(int $fileId): bool
    {
        $uploadedFile = $this->uploadedFileRepository->find($fileId);

        if (!$uploadedFile) {
            $this->logger->error("Uploaded file with ID $fileId not found");
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
            $this->logger->error("Error saving uploaded file entity: " . $e->getMessage());
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
            $this->logger->error("Error updating uploaded file status: " . $e->getMessage());
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

            // Generate a unique batch ID for this import operation
            $batchId = uniqid('batch_', true);
            $this->logger->info("Starting parallel processing of file {$uploadedFile->getFileName()} with batch ID $batchId");

            // Ensure chunks directory exists
            $chunksPath = $this->parameterBag->get('kernel.project_dir') . '/' . $this->parameterBag->get('chunks_path');
            $this->filesystem->mkdir($chunksPath, 0755);

            // Split the file into chunks
            $chunkFilenames = $this->splitFileIntoChunks($filePath, $chunksPath);
            if (empty($chunkFilenames)) {
                throw new \Exception("Failed to split file into chunks");
            }

            $this->logger->info("Split file into " . count($chunkFilenames) . " chunks");

            // Dispatch a message for each chunk
            foreach ($chunkFilenames as $chunkFilename) {
                $message = new ProcessUploadedFileChunkMessage($chunkFilename, $batchId, $uploadedFile->getId());
                $this->messageBus->dispatch($message);
            }

            // Dispatch a message to finalize the import after all chunks have been processed
            $finalizeMessage = new FinalizeCallsImportMessage($batchId, $uploadedFile->getId(), $chunkFilenames);
            $this->messageBus->dispatch($finalizeMessage);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error processing file: " . $e->getMessage());

            // Update the status to "failed"
            try {
                $uploadedFile->setStatus('failed');
                $uploadedFile->setErrorMessage($e->getMessage());
                $this->uploadedFileRepository->save($uploadedFile, true);
            } catch (\Exception $statusUpdateException) {
                $this->logger->error("Error updating uploaded file status: " . $statusUpdateException->getMessage());
            }

            return false;
        }
    }

    /**
     * Split a file into chunks using the split command
     * 
     * @return array Array of chunk filenames
     */
    private function splitFileIntoChunks(string $filePath, string $chunksPath): array
    {
        // Generate a unique prefix for the chunks
        $chunkPrefix = 'chunk_' . uniqid() . '_';
        $chunkPrefixPath = $chunksPath . '/' . $chunkPrefix;

        // Number of lines per chunk
        $linesPerChunk = 15;

        // Execute the split command
        $process = new Process([
            'split',
            '-l', (string)$linesPerChunk,
            '--additional-suffix=.csv',
            $filePath,
            $chunkPrefixPath
        ]);

        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Get the list of chunk files
        $chunkFiles = glob($chunksPath . '/' . $chunkPrefix . '*.csv');
        
        // Extract just the filenames without the path
        $chunkFilenames = array_map('basename', $chunkFiles);

        return $chunkFilenames;
    }
}