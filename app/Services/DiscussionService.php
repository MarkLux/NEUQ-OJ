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

    /**
     * 辅助方法
     */

    public function isTopicCreator(int $topicId,int $userId): bool
    {
        $topic = $this->discussionRepo->get($topicId)->first();
        if($userId == $topic->user_id)
            return true;
        else
            return false;
    }

    /**
     * 基本部分
     */

    public function addTopic(array $data):bool
    {
        $this->discussionRepo->insert($data);
    }

    public function deleteTopic(int $topicId):bool
    {
        $this->discussionRepo->delete($topicId);
    }

    public function updateTopic(int $topicId, array $condition):bool
    {
        $this->discussionRepo->update($condition , $topicId);
    }

//    public function searchTopicByAuthor(string $authorName)
//    {
//        $authorId = $this->userService->getUserBy('name',$authorName)->first();
//
//        if($authorId == null)
//            throw new UserNotExistException();
//        else {
//            $this->discussionRepo->getBy('user_id',$authorId);
//        }
//    }
//
    public function searchTopicCount(string $title): int
    {
        $pattern = '%'.$title.'%';//在这里定义模式串
        //未支持嵌套
        return $this->discussionRepo->getWhereLikeCount($pattern);
    }

    public function searchTopicByTitle(string $title,int $page =1,int $size =15)
    {
        $param = '%'.$title.'%';

        return $this->discussionRepo->getWhereLike($param,$page,$size);
    }

    /**
     * 回复管理
     */

    public function addReply(int $father, array $condition):bool
    {
        $condition['father'] = $father;
        return $this->discussionRepo->insert($condition);
    }

    /**
     * 置顶管理
     */

    public function stick(int $topicId):bool
    {
        $topic = $this->discussionRepo->get($topicId)->first();
        if($topic != null) {
            $data = ['stick' => 0];
            return $this->discussionRepo->update($data, $topicId) == 1;
        } else {
            throw new TopicNotExistException();
        }
    }

    public function unStick(int $topicId):bool
    {
        $topic = $this->discussionRepo->get($topicId)->first();
        if($topic != null) {
            $data = ['stick' => 0];
            return $this->discussionRepo->update($data, $topicId) == 1;
        } else {
            throw new TopicNotExistException();
        }
    }
}