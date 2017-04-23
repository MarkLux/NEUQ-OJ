<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午9:15
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class NewsRepository extends AbstractRepository
{
    use InsertWithIdTrait;

    function model()
    {
        return "NEUQOJ\Repository\Models\News";
    }

    public function getLatestNews(int $size,array $columns=['*'])
    {
        return $this->model
            ->where('importance','<>','0')
            ->orderBy('updated_at','desc')
            ->take($size)
            ->get($columns);
    }

    public function paginate(int $page = 1, int $size = 15, array $param = [], array $columns = ['*'])
    {
        if(!empty($param))
            return $this->model
                ->where($param)
                ->orderBy('created_at','desc')
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

    public function getTotalCount():int
    {
        return $this->model->all()->count();
    }
}