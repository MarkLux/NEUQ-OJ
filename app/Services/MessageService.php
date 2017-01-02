<?php
/**
 * Created by PhpStorm.
 * User: NEUQer
 * Date: 16/12/28
 * Time: 下午9:30
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\MessageRepository;

use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\Contracts\MessageServiceInterface;


class MessageService implements MessageServiceInterface
{



    private $messageRepo;
    private $userService;

    public function __construct(MessageRepository $messageRepository,UserService $userService)
    {
        $this->messageRepo = $messageRepository;
        $this->userService = $userService;
    }

    public function getMessage(int $messageId, array $columns = ['*'])
    {
        return $this->messageRepo->get($messageId,$columns)->fisrt();

    }

    public function getUnreadMessages(int $userId, int $page, int $size, array $columns = ['*'])
    {

        return $this->messageRepo->paginate($page,$size,['to_id'=>$userId,'is_read'=>0],$columns);

    }


    public function getUserMessageCount(int $userId): int
    {
        return $this->messageRepo->getMessageCount($userId);
    }


    public function getMessageBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->messageRepo->getBy($param,$value,$columns);
    }
    public function getUnreadMessagesCount(int $userId): int
    {
        return $this->messageRepo->getUnreadMessagesCount($userId);
    }
    public function getUserMessages(int $userId, int $page, int $size, array $columns = ['*'])
    {
        return $this->messageRepo->paginate($page,$size,['to_id'=>$userId],$columns);
    }
    public function sendMessage(int $from, int $to, array $data): int
    {
        $fromData = $this->userService->getUserById($from);
        $toData = $this->userService->getUserById($to);
        $message = [
            'from_id'=>$from,
            'to_id'=>$to,
            'from_name'=>$fromData['name'],
            'to_name'=>$toData['name'],
            'content'=>$data['content']
        ];
        return $this->messageRepo->insert($message);
    }

    public function deleteMessage(int $userId, int $messageId): bool
    {
        return $this->messageRepo->deleteWhere(['to_id'=>$userId]);
    }
}