<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午3:52
 */

namespace NEUQOJ\Repository\Eloquent;


use Illuminate\Support\Facades\DB;

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

    public function deleteWhereIn(string $param, array $data = [])
    {
        return $this->model->whereIn($param, $data)->delete();
    }

    public function getWhereCount(array $params)
    {
        return $this->model->where($params)->count();
    }

    //辅助方法
    public function getSolutionsIn(string $param1, string $value, string $param2, array $values, array $columns = ['*'])
    {
        return $this->model
            ->where($param1, $value)
            ->whereIn($param2, $values)
            ->get($columns);
    }

}