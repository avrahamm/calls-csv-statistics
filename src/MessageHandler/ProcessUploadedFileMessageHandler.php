<?php

namespace App\MessageHandler;

use App\Message\ProcessUploadedFileMessage;
use App\Service\CallsCsvProcessor;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class ProcessUploadedFileMessageHandler
{
    private CallsCsvProcessor $callsCsvProcessor;
    private LoggerInterface $logger;

    public function __construct(
        CallsCsvProcessor $callsCsvProcessor,
        LoggerInterface $logger
    ) {
        $this->callsCsvProcessor = $callsCsvProcessor;
        $this->logger = $logger;
    }

    public function __invoke(ProcessUploadedFileMessage $message)
    {
        $fileId = $message->getFileId();
        
        $this->logger->info('Processing uploaded file', ['file_id' => $fileId]);
        
        try {
            $result = $this->callsCsvProcessor->processUploadedFile($fileId);
            
            if ($result) {
                $this->logger->info('Successfully processed uploaded file', ['file_id' => $fileId]);
            } else {
                $this->logger->error('Failed to process uploaded file', ['file_id' => $fileId]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error processing uploaded file', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}