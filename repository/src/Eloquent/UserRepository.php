<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午8:42
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class UserRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\User";
    }

    use InsertWithIdTrait;

    public function deleteWhereIn(string $param,array $data)
    {
        return $this->model->whereIn($param,$data)->delete();
    }
}