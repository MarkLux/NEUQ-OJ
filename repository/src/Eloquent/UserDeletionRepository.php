<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:49
 */

namespace NEUQOJ\Repository\Eloquent;


class UserDeletionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserDeletion";
    }

    //覆盖父类方法
    function paginate(int $page = 1,int $size = 15,array $param = [],array $columns = ['*'])
    {
        if(!empty($param))
            return $this->model
                ->orderBy('created_at','desc')
                ->where($param)
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        else
            return $this->model
                ->orderBy('created_at','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
    }

    function getCount()
    {
        return $this->model->count();
    }
}