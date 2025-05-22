<?php

namespace App\Command;

use App\Service\ParallelCallsCsvProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:process-calls-csv-parallel',
    description: 'Process a CSV file containing call data using parallel processing',
)]
class ProcessCallsCsvParallelCommand extends Command
{
    private ParallelCallsCsvProcessor $parallelCallsCsvProcessor;

    public function __construct(ParallelCallsCsvProcessor $parallelCallsCsvProcessor)
    {
        $this->parallelCallsCsvProcessor = $parallelCallsCsvProcessor;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv-file', InputArgument::REQUIRED, 'Path to the CSV file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFilePath = $input->getArgument('csv-file');

        if (!file_exists($csvFilePath)) {
            $io->error(sprintf('File "%s" does not exist.', $csvFilePath));
            return Command::FAILURE;
        }

        $io->title('Processing calls data from CSV using parallel processing');
        $io->info(sprintf('Processing file: %s', $csvFilePath));

        $result = $this->parallelCallsCsvProcessor->processFilePath($csvFilePath);

        if ($result) {
            $io->success('CSV file processing started successfully. Check the logs for progress.');
            return Command::SUCCESS;
        } else {
            $io->error('Failed to start processing CSV file. Check the logs for more information.');
            return Command::FAILURE;
        }
    }
}