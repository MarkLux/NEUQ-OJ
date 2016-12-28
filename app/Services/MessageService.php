<?php
/**
 * Created by PhpStorm.
 * User: NEUQer
 * Date: 16/12/28
 * Time: 下午9:30
 */

namespace NEUQOJ\Services;

use NEUQOJ\Services\Contracts\MessageServiceInterface;


class MessageService implements MessageServiceInterface
{
    public function getMessage(int $messageId, array $columns = ['*'])
    {
        // TODO: Implement getMessage() method.
    }

    public function getUnreadMessages(int $userId, int $page, int $size, array $columns = ['*'])
    {
        // TODO: Implement getUnreadMessages() method.
    }


    public function getUserMessageCount(int $userId): int
    {
        // TODO: Implement getUserMessageCount() method.
    }


    public function getMessageBy(string $param, string $value, array $columns = ['*'])
    {
        // TODO: Implement getMessageBy() method.
    }
    public function getUnreadMessagesCount(int $userId): int
    {
        // TODO: Implement getUnreadMessagesCount() method.
    }
    public function getUserMessages(int $userId, int $page, int $size, array $columns = ['*'])
    {
        // TODO: Implement getUserMessages() method.
    }
    public function sendMessage(int $from, int $to, array $data): int
    {
        // TODO: Implement sendMessage() method.
    }

    public function deleteMessage(int $userId, int $messageId): bool
    {
        // TODO: Implement deleteMessage() method.
    }
}