<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tarantool\Client\Client;

#[AsCommand(
    name: 'app:cleanup-tarantool-messages',
    description: 'Removes messages exceeding the limit of 100 per dialog in Tarantool.'
)]
class CleanupTarantoolMessagesCommand extends Command
{

    public function __construct(
        private readonly Client $tarantool
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Starting cleanup of old messages in Tarantool...');

        try {
            $result = $this->tarantool->call('cleanup_old_messages');
            $deletedCount = $result[0]['deleted'] ?? 0;
            $io->info(sprintf('Successfully deleted %d old messages.', $deletedCount));

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error(sprintf('Error during cleanup: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
