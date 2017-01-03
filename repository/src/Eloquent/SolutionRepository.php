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

    //题目组通用排行榜功能
    public function getRankList(int $groupId)
    {
        return $this->model
            ->where('problem_group_id',$groupId)
            ->where('problem_num','>','0')
            ->leftJoin('users','users.id','=','solutions.user_id')
            ->select('users.id','users.name','solutions.result','solutions.judge_time','solutions.problem_num')
            //注意时间的选择标准，repo层维护的时间戳并不准确，这里先用judge_time来代替
            ->orderBy('users.id', 'desc')
            ->orderBy('solutions.created_at','desc')
            ->get();
    }
}