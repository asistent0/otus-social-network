<?php

namespace App\Controller;

use App\Controller\Payload\SendDialogRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Dialog\DialogService;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/dialog', name: 'app_dialog')]
final class DialogController extends AbstractController
{
    function __construct(
        private readonly DialogService $dialogService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws DBALException
     */
    #[Route('/{user_id}/list', name: '_list', methods: ['GET'])]
    public function list(string $user_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $friend = $this->userRepository->find($user_id);
        if (!$friend) {
            return $this->json('No user found', 404);
        }

        $messages = $this->dialogService->list($user, $friend);

        if (empty($messages)) {
            return $this->json([]);
        }

        return $this->json($messages);
    }

    /**
     * @throws Throwable
     */
    #[Route('/{user_id}/send', name: '_send', methods: ['POST'])]
    public function send(
        string $user_id,
        #[MapRequestPayload] SendDialogRequest $sendDialogRequest,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $friend = $this->userRepository->find($user_id);
        if (!$friend) {
            return $this->json('No user found', 404);
        }

        $this->dialogService->createDialog($user, $friend, $sendDialogRequest->text);

        return $this->json('OK');
    }
}
