<?php

namespace App\Controller;

use App\Controller\Payload\CreatePostRequest;
use App\Controller\Payload\UpdatePostRequest;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\Post\PostService;
use App\Service\Post\PostTransform;
use DateMalformedStringException;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/post', name: 'app_post')]
final class PostController extends AbstractController
{
    function __construct(
        private readonly PostService $postService,
        private readonly PostRepository $postRepository,
        private readonly PostTransform $postTransform,
    ) {
    }

    /**
     * @throws DBALException
     * @throws DateMalformedStringException
     */
    #[Route('/feed', name: '_feed', methods: ['GET'])]
    public function feed(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);

        if ($offset < 0) {
            $offset = 0;
        }
        if ($limit <= 0) {
            $limit = 10;
        }

        $postsData = $this->postService->list($user, $offset, $limit);

        if (empty($postsData)) {
            return $this->json([]);
        }

        return $this->json($postsData);
    }

    /**
     * @throws DBALException
     */
    #[Route('/create', name: '_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreatePostRequest $createPostRequest,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $post = $this->postService->createPost($user, $createPostRequest->text);

        return $this->json(['id' =>  $post->getId()], Response::HTTP_CREATED);
    }

    /**
     * @throws DBALException
     */
    #[Route('/get/{id}', name: '_get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $post = $this->postRepository->findOneById($id);
        if (!$post) {
            throw $this->createNotFoundException();
        }

        return $this->json($this->postTransform->getInfo($post));
    }

    /**
     * @throws DBALException
     */
    #[Route('/update', name: '_update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload] UpdatePostRequest $updatePostRequest,
    ): JsonResponse {
        $post = $this->postRepository->findOneById($updatePostRequest->id);
        if (!$post) {
            throw $this->createNotFoundException();
        }

        $this->postService->updatePost($updatePostRequest);

        return $this->json('OK');
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    #[Route('/delete/{id}', name: '_delete', methods: ['PUT'])]
    public function delete(string $id): JsonResponse
    {
        $post = $this->postRepository->findOneById($id);
        if (!$post) {
            throw $this->createNotFoundException();
        }

        $this->postService->removePost($post);

        return $this->json('OK');
    }
}
