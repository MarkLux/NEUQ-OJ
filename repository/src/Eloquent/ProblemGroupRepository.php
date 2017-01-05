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

    public function getContestCount(string $pattern)
    {
        return $this->model
            ->where('type','1')
            ->where('title','like',$pattern)
            ->count();
    }

    public function searchContest(string $pattern,int $page,int $size,array $columns =['*'])
    {
        return $this->model
            ->where('type','1')
            ->where('title','like',$pattern)
            ->skip($size * --$page)
            ->take($size)
            ->get($columns);
    }

}