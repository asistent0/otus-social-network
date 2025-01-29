<?php

namespace App\Controller;

use App\Exception\UserNotFoundException;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserController extends AbstractController
{

    function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/user/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->userService->createUser(
                firstName: $data['first_name'],
                lastName: $data['last_name'],
                password: $data['password'],
                birthDate: $data['birth_date'],
                gender: $data['gender'],
                biography: $data['biography'] ?? null,
                city: $data['city'],
            );

            return $this->json([
                'user_id' => $user->getUserIdentifier(),
            ]);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/user/get/{id}', name: 'user_get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $userData = $this->userService->getUser($id);

            return $this->json($userData);
        } catch (UserNotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @throws ExceptionInterface
     * @throws DBALException
     */
    #[Route('/user/search', name: 'user_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $firstName = $request->get('first_name', '');
        $lastName = $request->get('last_name', '');

        if (empty($firstName) && empty($lastName)) {
            return $this->json([]);
        }

        $usersData = $this->userService->search($firstName, $lastName);

        return $this->json($usersData);
    }
}
