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
            ->orderBy('updated_at','desc')
            ->take($size)
            ->get($columns);
    }
}