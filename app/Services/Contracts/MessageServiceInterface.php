<?php
/**
 * Created by PhpStorm.
 * User: NEUQer
 * Date: 16/12/28
 * Time: 下午8:11
 */

namespace NEUQOJ\Services\Contracts;

interface MessageServiceInterface
{
    function getMessage(int $messageId,array $columns = ['*']);
    //注意检查读取信息的权限

    function getMessageBy(string $param,string $value,array $columns = ['*']);

    function getUserMessageCount(int $userId):int;

    function getUserMessages(int $userId,int $page,int $size,array $columns = ['*']);

    function getUnreadMessagesCount(int $userId):int;

    function getUnreadMessages(int $userId,int $page,int $size,array $columns=['*']);

    function sendMessage(int $from,int $to,array $data):int;
    //发送一条信息，成功后返回信息的id

    function deleteMessage(int $userId,int $messageId):bool;
    //注意检查一下是否有权删除信息

    function getUserMessagesByMult(array $data,array $columns = ['*']);

}