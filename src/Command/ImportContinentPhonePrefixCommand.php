<?php

namespace App\Command;

use App\Entity\ContinentPhonePrefix;
use App\Repository\ContinentPhonePrefixRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-continent-phone-prefix',
    description: 'Import continent phone prefixes from a CSV file',
)]
class ImportContinentPhonePrefixCommand extends Command
{
    private const BATCH_SIZE = 20;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContinentPhonePrefixRepository $repository
    ) {
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

        $io->title('Importing continent phone prefixes from CSV');
        $io->progressStart();

        $handle = fopen($csvFilePath, 'r');
        if ($handle === false) {
            $io->error(sprintf('Could not open file "%s".', $csvFilePath));
            return Command::FAILURE;
        }

        // Skip header row
        fgetcsv($handle, 0, ",");

        $batchCount = 0;
        $totalCount = 0;

        while (($data = fgetcsv($handle, 0, ",")) !== false) {
            // CSV format: Country, Continent, Phone
            if (count($data) < 3) {
                $io->warning(sprintf('Skipping invalid row: %s', implode(', ', $data)));
                continue;
            }

            $io->info(sprintf('Processing row: %s', implode(', ', $data)));

            $continentCode = $data[1];
            $phonePrefix = $data[2];

            // Create or update entity
            $entity = $this->repository->find($phonePrefix) ?? new ContinentPhonePrefix();
            $entity->setPhonePrefix($phonePrefix);
            $entity->setContinentCode($continentCode);

            $this->repository->save($entity);

            $batchCount++;
            $totalCount++;

            // Flush every BATCH_SIZE entities
            if ($batchCount >= self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Clear the entity manager to free memory
                $batchCount = 0;
                $io->progressAdvance(self::BATCH_SIZE);
            }
        }

        // Flush remaining entities
        if ($batchCount > 0) {
            $this->entityManager->flush();
            $io->progressAdvance($batchCount);
        }

        fclose($handle);
        $io->progressFinish();

        $io->success(sprintf('Successfully imported %d continent phone prefixes.', $totalCount));

        return Command::SUCCESS;
    }
}
