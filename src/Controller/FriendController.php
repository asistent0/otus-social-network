<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Friend\FriendService;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Throwable;

#[Route('/friend', name: 'app_friend')]
final class FriendController extends AbstractController
{
    function __construct(
        private readonly FriendService $friendService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws DBALException
     * @throws ExceptionInterface
     */
    #[Route('/list', name: '_list', methods: ['GET'])]
    public function list(UserService $userService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $friends = $userService->friends($user);

        $result = [
            'count' => 0,
            'items' => [],
        ];

        if (empty($friends)) {
            return $this->json($result);
        }

        $result['count'] = count($friends);
        $result['items'] = $friends;

        return $this->json($result);
    }

    /**
     * @throws Throwable
     */
    #[Route('/set/{user_id}', name: '_set', methods: ['PUT'])]
    public function set(string $user_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $friend = $this->userRepository->find($user_id);

        $this->friendService->addFriend($user, $friend);

        return $this->json('OK');
    }

    /**
     * @throws DBALException
     */
    #[Route('/delete/{user_id}', name: '_delete', methods: ['PUT'])]
    public function delete(string $user_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $friend = $this->userRepository->find($user_id);

        if (!$friend) {
            return $this->json('OK');
        }

        $this->friendService->removeFriend($user, $friend);

        return $this->json('OK');
    }
}
