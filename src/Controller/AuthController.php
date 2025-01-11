<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Uid\Uuid;

class AuthController extends AbstractController
{

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $jwtManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id']) || !isset($data['password'])) {
            return $this->json([
                'error' => 'Id and password are required.',
            ], 400);
        }

        $user = $userRepository->findOneById($data['id']);
        if (!$user) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        // Проверяем пароль
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
        ]);
    }
}
