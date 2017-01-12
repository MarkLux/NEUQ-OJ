<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17-1-2
 * Time: 下午12:59
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class MessageRepository extends  AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Message";
    }

    function getMessageCount($userId)
    {
        return $this->model->where('to_id',$userId)->count();
    }

    function getUnreadMessageCount($userId)
    {
        return $this->model->where(['to_id'=>$userId,'is_read'=>0])->count();
    }
    use InsertWithIdTrait;
}