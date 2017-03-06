<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/12/18
 * Time: 下午2:53
 */

namespace NEUQOJ\Repository\Eloquent;



class DiscussionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Discussion";
    }

    //获取模糊字段数
    function getWhereLikeCount(string $pattern):int
    {
        return $this->model
            ->where('title','like',$pattern)
            ->count();
    }

    //获取模糊字段，分页
    function getWhereLike(string $param , int $page = 1, int $size = 5 ,array $columns = ['*'])
    {
        if(!empty($size)) {
            return $this->model
                ->where('title','like',$param)
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        }
    }
}