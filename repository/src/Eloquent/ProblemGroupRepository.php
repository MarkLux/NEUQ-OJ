<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:14
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\InsertWithIdTrait;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class ProblemGroupRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\ProblemGroup";
    }

    use InsertWithIdTrait;

//    use SoftDeletionTrait;

    public function getProblemGroupCount(int $type)
    {
        return $this->model
            ->where('type',$type)
            ->count();
    }

    public function getHomeworkCount(int $groupId)
    {
        return $this->model
            ->where('type',2)
            ->where('user_group_id',$groupId)
            ->count();
    }

    public function getProblemGroupCountLike(int $type,string $pattern)
    {
        return $this->model
            ->where('type',$type)
            ->where('title','like',$pattern)
            ->count();
    }

    public function searchProblemGroup(int $type,string $pattern,int $page,int $size,array $columns =['*'])
    {
        return $this->model
            ->where('type',$type)
            ->where('title','like',$pattern)
            ->skip($size * --$page)
            ->take($size)
            ->get($columns);
    }

    //覆盖分页方法，取出数据时根据时间进行排序
    public function paginate(int $page = 1, int $size = 15, array $param = [], array $columns = ['*'])
    {
        if(!empty($param))
            return $this->model
                ->where($param)
                ->orderBy('created_at','desc')
                ->orderBy('start_time','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        else
            return $this->model
                ->orderBy('created_at','desc')
                ->orderBy('start_time','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
    }

}