<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午3:52
 */

namespace NEUQOJ\Repository\Eloquent;


class SolutionRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\Solution";
    }

    public function getTotalCount()
    {
        return $this->model->all()->count();
    }

    public function deleteWhereIn(string $param,array $data = [])
    {
        return $this->model->whereIn($param,$data)->delete();
    }
}