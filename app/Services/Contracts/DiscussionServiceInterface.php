<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/12/14
 * Time: 下午8:14
 */

namespace NEUQOJ\Services\Contracts;

interface DiscussionInterface
{
    function addTopic(array $data);

    function deleteTopic(int $topicId);

    function updateTopic(int $topicId , array $condition);

    /**
     * 查找
     */

    function searchTopicByAuthor(string $authorName);

    function searchTopicByTitle(string $title);

    /**
     * 回复
     */

    function addReply(int $father , array $condition);

    function deleteReply(int $topicId);

}