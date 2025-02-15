<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\User;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/friend', name: 'app_friend')]
final class FriendController extends AbstractController
{
    function __construct(
        private readonly FriendRepository $friendRepository,
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
     * @throws DBALException
     * @throws ExceptionInterface
     */
    #[Route('/set/{user_id}', name: '_set', methods: ['PUT'])]
    public function set(string $user_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $friend = $this->userRepository->find($user_id);
        $friends = $this->userRepository->getFriends($user);

        $isExist = false;
        foreach ($friends as $existFriend) {
            if ($existFriend->getId()->toString() === $user_id) {
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
        }

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

        $this->friendRepository->remove($user, $friend);

        return $this->json('OK');
    }
}
