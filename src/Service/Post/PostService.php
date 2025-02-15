<?php

namespace App\Service\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class PostService
{
    public function __construct(
        private ValidatorInterface $validator,
        private PostRepository $postRepository,
        private PostTransform $postTransform,
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

        return $post;
    }

    /**
     * @throws DBALException
     */
    public function list(int $offset, int $limit): array
    {
        $posts = $this->postRepository->list($offset, $limit);
        $data = [];

        foreach ($posts as $post) {
            $data[] = $this->postTransform->getInfo($post);
        }

        return $data;
    }
}
