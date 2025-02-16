<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\FeedCacheService;
use DateMalformedStringException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:rebuild-feeds', 'Rebuild all feed caches from database')]
class RebuildFeedsCommand extends Command
{
    private const int BATCH_SIZE = 1000;

    public function __construct(
        private readonly FeedCacheService $feedCacheService,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * @throws DateMalformedStringException
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $totalUsers = $this->userRepository->count([]);
        $progressBar = new ProgressBar($output, $totalUsers);
        $progressBar->start();

        $page = 1;
        do {
            $users = $this->userRepository->getUsersBatch(self::BATCH_SIZE, $page);

            foreach ($users as $user) {
                $this->feedCacheService->rebuildFeedFromDatabase($user);
                $progressBar->advance();

                $this->entityManager->detach($user);
                $this->entityManager->clear();
            }

            $page++;
        } while (!empty($users));

        $progressBar->finish();
        $output->writeln("\nAll feeds rebuilt successfully");

        return Command::SUCCESS;
    }
}
