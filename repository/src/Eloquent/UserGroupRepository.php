<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:54
 */

namespace NEUQOJ\Repository\Eloquent;


class UserGroupRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserGroup";
    }

    function insertWithId(array $data)
    {
        return $this->model->insertGetId($data);
    }

    function getWhereLikeCount(string $pattern):int
    {
        //在三个字段中搜索

        return $this->model
            ->where('name','like',$pattern)
            ->orWhere('owner_name','like',$pattern)
            ->orWhere('description','like',$pattern)
            ->count();
    }

    //简易like搜索
    function getWhereLike(string $pattern,int $page = 1,int $size = 15,array $columns = ['*'])
    {
        if(!empty($size))
        {
            return $this->model
                ->where('name','like',$pattern)
                ->orWhere('owner_name','like',$pattern)
                ->orWhere('description','like',$pattern)
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        }
    }
}