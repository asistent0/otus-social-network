<?php

namespace App\MessageHandler;

use App\Message\AddToFeedMessage;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\FeedCacheService;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[AsMessageHandler]
readonly class AddToFeedHandler
{
    public function __construct(
        private FeedCacheService       $feedCacheService,
        private UserRepository $userRepository,
        private PostRepository $postRepository,
    ) {
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function __invoke(AddToFeedMessage $message): void
    {
        $user = $this->userRepository->findOneById($message->getUserId());
        $post = $this->postRepository->find($message->getPostId());

        $this->feedCacheService->addPostToFriendsFeeds($user, $post);
    }
}
