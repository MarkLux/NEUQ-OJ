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

    //组织题目的部分
//    public function getUserAcIds(int $userId,array $problemIds)
//    {
//        return $this->model
//            ->where('user_id',$userId)
//            ->whereIn('problem_id',$problemIds)
//            ->where('result',4)
//            ->groupBy('problem_id')
//            ->get(['problem_id']);
//    }
//
//    public function getUserSubIds(int $userId,array $problemIds)
//    {
//        return $this->model
//            ->where('user_id',$userId)
//            ->whereIn('problem_id',$problemIds)
//            ->groupBy('problem_id')
//            ->get(['problem_id']);
//    }

    public function getAllSolutions(int $page = 1,int $size = 15,array $param = [])
    {
        if(!empty($param))
            return $this->model
                ->leftJoin('users','solutions.user_id','=','users.id')
                ->where($param)
                ->where('solutions.problem_id','>','0')
                ->select('solutions.id','solutions.problem_id','solutions.user_id','solutions.time','solutions.memory','solutions.result','solutions.language','solutions.code_length','solutions.created_at','users.name')
                ->orderBy('created_at','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get();
        else
            return $this->model
                ->leftJoin('users','solutions.user_id','=','users.id')
                ->where('solutions.problem_id','>','0')
                ->select('solutions.id','solutions.problem_id','solutions.user_id','solutions.time','solutions.memory','solutions.result','solutions.language','solutions.code_length','solutions.created_at','users.name')
                ->orderBy('created_at','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get();
    }

    public function getSolution(int $id)
    {
        return $this->model
            ->leftJoin('users','solutions.user_id','=','users.id')
            ->select('solutions.id','solutions.problem_id','solutions.user_id','solutions.time','solutions.memory','solutions.result','solutions.language','solutions.code_length','solutions.created_at','users.name')
            ->where('solutions.id',$id)
            ->get();
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
            ->where('problem_num','>=','0')
            ->leftJoin('users','users.id','=','solutions.user_id')
            ->select('users.id','users.name','solutions.result','solutions.created_at','solutions.problem_num')
            //注意时间的选择标准，judge_time是批量更新的，应该根据创建时间来排序
            ->orderBy('users.id', 'desc')
            ->orderBy('solutions.created_at','desc')
            ->get();
    }

}