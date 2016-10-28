<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午7:20
 */

namespace NEUQOJ\Repository\Eloquent;


class PrivilegeRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Privilege";
    }

    function getIn($param,array $data,array $columns = ['*'])
    {
        return $this->model->whereIn($param,$data)->get($columns);
    }
}