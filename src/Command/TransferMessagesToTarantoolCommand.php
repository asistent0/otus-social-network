<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tarantool\Client\Client;

#[AsCommand(
    name: 'app:transfer-messages-to-tarantool',
    description: 'Transfers message data from SQL database to Tarantool in batches.'
)]
class TransferMessagesToTarantoolCommand extends Command
{

    public function __construct(
        private readonly Client     $tarantool,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Starting transfer of messages to Tarantool...');

        $dialogIds = $this->connection->fetchAllAssociative('SELECT DISTINCT dialog_id FROM message');
        $totalDialogs = count($dialogIds);
        $io->info(sprintf('Total dialogs to transfer: %d', $totalDialogs));

        $progressBar = new ProgressBar($output, $totalDialogs);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        foreach ($dialogIds as $dialog) {
            $dialogId = $dialog['dialog_id'];
            $query = 'SELECT id, dialog_id, sender_id, participant1_id, text, created_at 
                      FROM message 
                      WHERE dialog_id = :dialog_id 
                      ORDER BY created_at DESC 
                      LIMIT 100';
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue('dialog_id', $dialogId, ParameterType::INTEGER);
            $result = $stmt->executeQuery();

            $batch = [];
            while ($row = $result->fetchAssociative()) {
                $batch[] = [
                    (int)$row['id'],
                    (int)$row['dialog_id'],
                    $row['sender_id'],
                    $row['participant1_id'],
                    $row['text'],
                    strtotime($row['created_at']),
                ];
            }
            if (!empty($batch)) {
                try {
                    $this->tarantool->call('insert_many', $batch);
                } catch (Exception $e) {
                    $io->error(sprintf('Error inserting messages for dialog %d: %s', $dialogId, $e->getMessage()));
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->info("\nMessages successfully transferred to Tarantool.");

        return Command::SUCCESS;
    }
}
