<?php

namespace App\Service\Dialog;

use App\Entity\Dialog;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\DialogRepository;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Tarantool\Client\Client;

readonly class DialogService
{
    public function __construct(
        private DialogTransform $dialogTransform,
        private DialogRepository $dialogRepository,
        private MessageRepository $messageRepository,
        private Client $tarantool,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function createDialog(
        User $user,
        User $friend,
        string $text,
    ): bool {
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
        $messageId = $this->messageRepository->save($message, $dialogData['id'], $dialogData['participant1_id']);

        try {
            $result = $this->tarantool->call(
                'send_message',
                $messageId,
                $dialogData['id'],
                $user->getId()->toString(),
                $friend->getId()->toString(),
                $text,
            );

            if (!empty($result)) {
                $result = $result[0];
            }

            return $result['success'];
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @throws DBALException
     */
    public function list(User $user, User $friend): array
    {
        $dialog = $this->dialogRepository->findDialogBetweenUsers($user, $friend);

        try {
            $messages = $this->tarantool->call('list_messages', $dialog['id']);
        } catch (Exception) {
            return [];
        }
        if (!empty($messages)) {
            $messages = $messages[0];
        }

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
