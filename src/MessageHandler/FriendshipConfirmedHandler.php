<?php

namespace App\MessageHandler;

use App\Message\FriendshipConfirmedMessage;
use App\Repository\PostRepository;
use App\Repository\UserFeedRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[AsMessageHandler]
readonly class FriendshipConfirmedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private UserFeedRepository $userFeedRepository,
    ) {}

    /**
     * @param FriendshipConfirmedMessage $event
     * @return void
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function __invoke(FriendshipConfirmedMessage $event): void
    {
        $user = $this->userRepository->findOneById($event->getFriendId());
        $posts = $this->postRepository->postList(
            $user,
            0,
            1000
        );

        foreach ($posts as $post) {
            $this->userFeedRepository->addPostsToFeeds(
                [$event->getUserId()],
                $post['id'],
                $post['created_at'],
            );
        }
    }
}
