<?php

namespace App\MessageHandler;

use App\Message\NewPostMessage;
use App\Repository\FriendRepository;
use App\Repository\UserFeedRepository;
use Doctrine\DBAL\Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
readonly class NewPostHandler
{
    public function __construct(
        private FriendRepository $friendRepository,
        private UserFeedRepository $userFeedRepository,
        private CacheInterface $cache
    ) {}

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __invoke(NewPostMessage $event): void
    {
        $friends = $this->cache->get(
            "user_friends_{$event->getUserId()->toString()}",
            function() use ($event) {
                return $this->friendRepository->findFriendIds($event->getUserId());
            }
        );

        $this->userFeedRepository->addPostsToFeeds(
            $friends,
            $event->getPostId(),
            $event->getCreatedAt()
        );
    }
}
