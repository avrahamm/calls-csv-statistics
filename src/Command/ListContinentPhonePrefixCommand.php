<?php

namespace App\Command;

use App\Repository\ContinentPhonePrefixRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-continent-phone-prefix',
    description: 'List all continent phone prefixes',
)]
class ListContinentPhonePrefixCommand extends Command
{
    public function __construct(
        private ContinentPhonePrefixRepository $repository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Continent Phone Prefixes');

        $prefixes = $this->repository->findAll();

        if (empty($prefixes)) {
            $io->warning('No continent phone prefixes found.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($prefixes as $prefix) {
            $rows[] = [
                $prefix->getPhonePrefix(),
                $prefix->getContinentCode(),
            ];
        }

        $io->table(
            ['Phone Prefix', 'Continent Code'],
            $rows
        );

        $io->success(sprintf('Found %d continent phone prefixes.', count($prefixes)));

        return Command::SUCCESS;
    }
}