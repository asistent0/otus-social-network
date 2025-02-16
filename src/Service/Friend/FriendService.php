<?php

namespace App\Service\Friend;

use App\Entity\Friend;
use App\Entity\User;
use App\Message\UpdateFeedOnFriendAddMessage;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Service\FeedCacheService;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

readonly class FriendService
{
    function __construct(
        private FriendRepository $friendRepository,
        private UserRepository $userRepository,
        private FeedCacheService $feedCacheService,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function addFriend(User $user, User $friend): void
    {
        $friends = $this->userRepository->getFriends($user);

        $isExist = false;
        foreach ($friends as $existFriend) {
            if ($existFriend->getId()->toString() === $friend->getId()->toString()) {
                $isExist = true;
                break;
            }
        }

        if (!$isExist) {
            $newFriend = new Friend()
                ->setUser($user)
                ->setFriend($friend);
            $user->addFriend($newFriend);
            $friend->addFriendedBy($newFriend);
            $this->friendRepository->save($newFriend);

            $this->bus->dispatch(new UpdateFeedOnFriendAddMessage(
                $user->getId()->toString(),
                $friend->getId()->toString(),
            ));
        }
    }

    /**
     * @throws DBALException
     */
    public function removeFriend(User $user, User $friend): void
    {
        $this->feedCacheService->invalidateFriendPosts($user, $friend);
        $this->friendRepository->remove($user, $friend);
    }
}
