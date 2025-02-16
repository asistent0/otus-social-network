<?php

namespace App\Service\Post;

use App\Controller\Payload\UpdatePostRequest;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\FeedCacheService;
use DateMalformedStringException;
use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class PostService
{
    public function __construct(
        private ValidatorInterface $validator,
        private PostRepository $postRepository,
        private PostTransform $postTransform,
        private FeedCacheService $feedCacheService,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function createPost(
        User $user,
        string $text,
    ): Post {
        $post = new Post()
            ->setUser($user)
            ->setText($text);

        $errors = $this->validator->validate($post);
        if (count($errors) > 0) {
            throw new InvalidArgumentException((string) $errors);
        }

        $this->postRepository->save($post);

        $this->feedCacheService->addPostToFriendsFeeds($post->getUser(), $post, true);

        return $post;
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function removePost(array $post): void
    {
        $this->feedCacheService->removePostFromFeeds($post);

        $this->postRepository->remove($post['id']);
    }

    /**
     * @throws DBALException
     */
    public function updatePost(UpdatePostRequest $updatePostRequest): void
    {
        $this->postRepository->update($updatePostRequest->id, $updatePostRequest->text);
        $post = $this->postRepository->findOneById($updatePostRequest->id);
        $this->feedCacheService->updatePostInFeeds($post);
    }

    /**
     * @throws DBALException
     * @throws DateMalformedStringException
     */
    public function list(User $user, int $offset, int $limit): array
    {
        $posts = $this->feedCacheService->getFeed($user, $offset, $limit);

        $data = [];
        foreach ($posts as $post) {
            $data[] = $this->postTransform->getInfo($post);
        }

        return $data;
    }
}
