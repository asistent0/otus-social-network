<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Message\AddToFeedMessage;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Exception as DBALException;
use Predis\Client;
use Predis\Pipeline\Pipeline;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Throwable;

readonly class FeedCacheService
{
    const string USER_FEED_KEY = 'user_feed:';
    const string POST_KEY = 'post:';
    const int LIMIT_POST = 1000;

    public function __construct(
        private Client $postCache,
        private PostRepository $postRepository,
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function addPostToFriendsFeeds(User $author, Post $post, bool $async = false): void
    {
        if ($async) {
            $this->bus->dispatch(new AddToFeedMessage(
                $author->getId()->toString(),
                $post->getId()->toString(),
            ));
            return;
        }

        $postId = $post->getId()->toString();

        $this->postCache->hmset(self::POST_KEY . $postId, [
            'id' => $postId,
            'user_id' => $post->getUser()->getId()->toString(),
            'text' => $post->getText(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $friends = $author->getFriends();
        $timestamp = $post->getCreatedAt()->getTimestamp();
        $pipeline = $this->postCache->pipeline();
        foreach ($friends as $friend) {
            $feedKey = self::USER_FEED_KEY . $friend->getId()->toString();
            $pipeline->zadd($feedKey, [$postId => $timestamp]);
            $pipeline->zremrangebyrank($feedKey, 0, -(self::LIMIT_POST + 1));
        }
        $pipeline->execute();
    }

    /**
     * @throws DBALException
     * @throws DateMalformedStringException
     */
    public function getFeed(User $user, int $offset, int $limit = self::LIMIT_POST): array
    {
        $feedKey = self::USER_FEED_KEY . $user->getId()->toString();
        if (!$this->postCache->exists($feedKey)) {
            $this->rebuildFeedFromDatabase($user);
        }

        $postIds = $this->postCache->zrevrange($feedKey, $offset, $offset + $limit - 1);
        $posts = [];

        foreach ($postIds as $postId) {
            if ($postData = $this->postCache->hgetall(self::POST_KEY . $postId)) {
                $posts[] = $postData;
            } else {
                $this->postCache->zrem($feedKey, $postId);
            }
        }

        return $posts;
    }

    /**
     * @throws Throwable
     */
    public function addFriendPosts(User $user, User $newFriend): void
    {
        $feedKey = self::USER_FEED_KEY . $user->getId()->toString();
        $posts = $this->postRepository->postList($newFriend, 0, self::LIMIT_POST);

        $pipeline = $this->postCache->pipeline();

        foreach ($posts as $post) {
            $postId = $post['id'];
            $timestamp = new DateTimeImmutable($post['created_at'])->getTimestamp();

            $pipeline->zadd($feedKey, [$postId => $timestamp]);

            if (!$this->postCache->exists(self::POST_KEY . $postId)) {
                $this->addPostToCache($pipeline, $post);
            }
        }

        $pipeline->zremrangebyrank($feedKey, 0, -(self::LIMIT_POST + 1));
        $pipeline->execute();
    }

    /**
     * @throws DBALException
     * @throws DateMalformedStringException
     */
    public function rebuildFeedFromDatabase(User $user): void
    {
        $posts = $this->postRepository->postListFriend($user, 0, self::LIMIT_POST);
        $feedKey = self::USER_FEED_KEY . $user->getId()->toString();

        $pipeline = $this->postCache->pipeline();
        $pipeline->del($feedKey);
        foreach ($posts as $post) {
            $postId = $post['id'];
            $timestamp = new DateTimeImmutable($post['created_at'])->getTimestamp();
            $pipeline->zadd($feedKey, [$postId => $timestamp]);
            $this->addPostToCache($pipeline, $post);
        }
        $pipeline->zremrangebyrank($feedKey, 0, -(self::LIMIT_POST + 1));
        $pipeline->execute();
    }

    public function invalidateFriendPosts(User $user, User $exFriend): void
    {
        $feedKey = self::USER_FEED_KEY . $user->getId()->toString();
        $exFriendId = $exFriend->getId()->toString();

        $postIds = $this->postCache->zrevrange($feedKey, 0, -1);

        $toRemove = [];
        foreach ($postIds as $postId) {
            $userId = $this->postCache->hget(self::POST_KEY . $postId, 'user_id');
            if ($userId === $exFriendId) {
                $toRemove[] = $postId;
            }
        }

        if (!empty($toRemove)) {
            $pipeline = $this->postCache->pipeline();
            foreach ($toRemove as $postId) {
                $pipeline->zrem($feedKey, $postId);
            }
            $pipeline->execute();
        }
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function removePostFromFeeds(array $post): void
    {
        $postId = $post['id'];
        $user = $this->userRepository->findOneById($post['user_id']);
        $friends = $this->userRepository->getFriends($user);

        foreach ($friends as $friend) {
            $this->postCache->zrem(self::USER_FEED_KEY . $friend->getId()->toString(), $postId);
        }
        $this->postCache->del(self::POST_KEY . $postId);
    }

    public function updatePostInFeeds(array $post): void
    {
        $postKey = self::POST_KEY . $post['id'];
        $this->postCache->hmset($postKey, ['text' => $post['text']]);
    }

    private function addPostToCache(Pipeline $pipeline, array $post): void
    {
        $postId = $post['id'];
        $pipeline->hmset(self::POST_KEY . $postId, [
            'id' => $postId,
            'user_id' => $post['user_id'],
            'text' => $post['text'],
            'created_at' => $post['created_at'],
        ]);
    }
}
