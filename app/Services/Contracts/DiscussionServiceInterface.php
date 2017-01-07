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
    function isTopicCreator(int $topicId,int $userId):bool;

    function addTopic(array $data):bool;

    function deleteTopic(int $topicId):bool;

    function updateTopic(int $topicId , array $condition):bool;

    /**
     * 查找 ，暂定为全局，不分题号
     */

//    function searchTopicByAuthor(string $authorName);
//
//    function searchTopicByTitle(string $title);

    function searchTopicByTitle(string $title,int $page = 1,int $size = 15);

    function searchTopicCount(string $title):int;

    /**
     * 回复
     */

    function addReply(int $father , array $condition):bool;

    /**
     * 置顶
     */

    function stick(int $topicId):bool;

    function unStick(int $topicId):bool;
}