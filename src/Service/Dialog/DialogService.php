<?php

namespace App\Service\Dialog;

use App\Entity\Dialog;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\DialogRepository;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Exception as DBALException;

readonly class DialogService
{
    public function __construct(
        private DialogTransform $dialogTransform,
        private DialogRepository $dialogRepository,
        private MessageRepository $messageRepository,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function createDialog(
        User $user,
        User $friend,
        string $text,
    ): void {
        $dialog = $this->dialogRepository->findDialogBetweenUsers($user, $friend);

        if (!$dialog) {
            $dialog = new Dialog($user, $friend);
            $this->dialogRepository->save($dialog);
            $dialogData = $this->dialogRepository->findDialogBetweenUsers($user, $friend);
        } else {
            $dialogData = $dialog;
        }

        $message = new Message()
            ->setText($text)
            ->setSender($user);
        $this->messageRepository->save($message, $dialogData['id'], $dialogData['participant1_id']);
    }

    /**
     * @throws DBALException
     */
    public function list(User $user, User $friend): array
    {
        $messages = $this->messageRepository->findMessagesBetweenUsers($user, $friend);

        $data = [];
        foreach ($messages as $message) {
            $to = $user->getId()->toString();
            if ($to === $message['sender_id']) {
                $to = $friend->getId()->toString();
            }
            $data[] = $this->dialogTransform->getInfo($message, $to);
        }

        return $data;
    }
}
