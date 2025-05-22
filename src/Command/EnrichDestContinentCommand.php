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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:enrich-dest-continent',
    description: 'Enrich dest_continent for calls with empty dest_continent values',
)]
class EnrichDestContinentCommand extends Command
{
    private CallRepository $callRepository;
    private MessageBusInterface $messageBus;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        CallRepository $callRepository,
        MessageBusInterface $messageBus,
        ParameterBagInterface $parameterBag
    ) {
        parent::__construct();
        $this->callRepository = $callRepository;
        $this->messageBus = $messageBus;
        $this->parameterBag = $parameterBag;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uploaded-file-id', InputArgument::REQUIRED, 'The ID of the uploaded file')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Number of calls to process in each batch', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uploadedFileId = $input->getArgument('uploaded-file-id');
        $batchSize = (int) $input->getOption('batch-size');

        $io->title('Enriching dest_continent for calls');
        $io->text("Processing calls for uploaded file ID: $uploadedFileId");

        // Check if there are any calls with empty dest_continent
        $totalPhones = $this->callRepository->countUniquePhonesByUploadedFileId($uploadedFileId);

        if ($totalPhones === 0) {
            $io->success('No calls with empty dest_continent found for this uploaded file.');
            return Command::SUCCESS;
        }

        $io->text("Found $totalPhones unique dialed numbers to process");
        $io->text("Will process in batches of $batchSize");

        // Dispatch an initial message to start processing
        // The handler will fetch the unique phones in batches and dispatch new messages as needed
        $offset = $this->parameterBag->get('enrich_dest_continent_offset');
        $message = new EnrichDestContinentMessage($uploadedFileId, 0, $offset, $offset);
        $this->messageBus->dispatch($message);

        $io->success('Message dispatched to enrich dest_continent for calls');

        return Command::SUCCESS;
    }
}
