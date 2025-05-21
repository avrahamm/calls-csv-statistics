<?php

namespace App\Command;

use App\Message\EnrichDestContinentMessage;
use App\Repository\CallRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:enrich-dest-continent',
    description: 'Enrich dest_continent for calls with empty dest_continent values',
)]
class EnrichDestContinentCommand extends Command
{
    private CallRepository $callRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        CallRepository $callRepository,
        MessageBusInterface $messageBus
    ) {
        parent::__construct();
        $this->callRepository = $callRepository;
        $this->messageBus = $messageBus;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uploaded-file-id', InputArgument::REQUIRED, 'The ID of the uploaded file')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Number of calls to process in each batch', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uploadedFileId = $input->getArgument('uploaded-file-id');
        $batchSize = $input->getOption('batch-size');

        $io->title('Enriching dest_continent for calls');
        $io->text("Processing calls for uploaded file ID: $uploadedFileId");

        // Get all unique dialed numbers for calls with empty dest_continent
        $connection = $this->callRepository->getEntityManager()->getConnection();
        $sql = '
            SELECT DISTINCT dialed_number
            FROM calls
            WHERE uploaded_file_id = :uploadedFileId
            AND dest_continent IS NULL
        ';
        $stmt = $connection->executeQuery($sql, ['uploadedFileId' => $uploadedFileId]);
        $uniqueDialedNumbers = array_map(function ($row) {
            return $row['dialed_number'];
        }, $stmt->fetchAllAssociative());

        $totalPhones = count($uniqueDialedNumbers);
        if ($totalPhones === 0) {
            $io->success('No calls with empty dest_continent found for this uploaded file.');
            return Command::SUCCESS;
        }

        $io->text("Found $totalPhones unique dialed numbers to process");

        // Dispatch a message to process the unique dialed numbers
        $message = new EnrichDestContinentMessage($uploadedFileId, $uniqueDialedNumbers);
        $this->messageBus->dispatch($message);

        $io->success('Message dispatched to enrich dest_continent for calls');

        return Command::SUCCESS;
    }
}