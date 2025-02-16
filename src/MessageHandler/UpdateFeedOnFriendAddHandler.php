<?php

namespace App\MessageHandler;

use App\Message\UpdateFeedOnFriendAddMessage;
use App\Repository\UserRepository;
use App\Service\FeedCacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class UpdateFeedOnFriendAddHandler
{
    public function __construct(
        private FeedCacheService $feedCacheService,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @param UpdateFeedOnFriendAddMessage $message
     * @throws Throwable
     */
    public function __invoke(UpdateFeedOnFriendAddMessage $message): void
    {
        $user = $this->userRepository->findOneById($message->getUserId());
        $friend = $this->userRepository->findOneById($message->getFriendId());

        $this->feedCacheService->addFriendPosts($user, $friend);
    }
}
