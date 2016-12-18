<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/12/18
 * Time: 下午2:00
 */

namespace NEUQOJ\Services;

use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Repository\Eloquent\DiscussionRepository;
use NEUQOJ\Services\Contracts\DiscussionInterface;

class DiscussionService implements DiscussionInterface
{
    private $discussionRepo;
    private $userService;

    public function __construct(discussionRepository $discussionRepository , UserService $userService)
    {
        $this->discussionRepo = $discussionRepository;
        $this->userService = $userService;
    }

    public function addTopic(array $data)
    {
        $this->discussionRepo->insert($data);
    }

    public function deleteTopic(int $topicId)
    {
        $this->discussionRepo->delete($topicId);
    }

    public function updateTopic(int $topicId, array $condition)
    {
        $this->discussionRepo->update($condition , $topicId);
    }

    public function searchTopicByAuthor(string $authorName)
    {
        $authorId = $this->userService->getUserBy('name',$authorName)->first();

        if($authorId == null)
            throw new UserNotExistException();
        else {
            $this->discussionRepo->getBy('user_id',$authorId);
        }
    }

    public function searchTopicByTitle(string $title)
    {
        $this->discussionRepo->getBy('title',$title);
    }

    public function addReply(int $father, array $condition)
    {
        $condition['father'] = $father;
        $this->discussionRepo->insert($condition);
    }
}