<?php

namespace App\Command;

use App\Message\ProcessUploadedFileMessage;
use App\Repository\UploadedFileRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:process-pending-calls-files',
    description: 'Process all pending calls CSV files',
)]
class ProcessPendingCallsFilesCommand extends Command
{
    private UploadedFileRepository $uploadedFileRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        UploadedFileRepository $uploadedFileRepository,
        MessageBusInterface $messageBus
    ) {
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->messageBus = $messageBus;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Processing pending calls files');
        
        // Find all pending files
        $pendingFiles = $this->uploadedFileRepository->findByStatus('pending');
        
        if (empty($pendingFiles)) {
            $io->info('No pending files found.');
            return Command::SUCCESS;
        }
        
        $io->info(sprintf('Found %d pending files.', count($pendingFiles)));
        
        // Process each file
        foreach ($pendingFiles as $file) {
            $io->info(sprintf('Dispatching job for file: %s (ID: %d)', $file->getFileName(), $file->getId()));
            
            // Dispatch a message to process the file
            $this->messageBus->dispatch(new ProcessUploadedFileMessage($file->getId()));
        }
        
        $io->success('All pending files have been queued for processing.');
        
        return Command::SUCCESS;
    }
}