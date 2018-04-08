<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午3:52
 */

namespace NEUQOJ\Repository\Eloquent;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function getAllSolutions(int $page = 1, int $size = 15, array $param = [])
    {
        if (!empty($param))
            return $this->model
                ->leftJoin('users', 'solutions.user_id', '=', 'users.id')
                ->where($param)
                ->where('solutions.problem_id', '>', '0')
                ->select('solutions.id', 'solutions.pass_rate', 'solutions.problem_id', 'solutions.user_id', 'solutions.time', 'solutions.memory', 'solutions.result', 'solutions.language', 'solutions.code_length', 'solutions.created_at', 'solutions.judger', 'users.name')
                ->orderBy('created_at', 'desc')
                ->skip($size * --$page)
                ->take($size)
                ->get();
        else
            return $this->model
                ->leftJoin('users', 'solutions.user_id', '=', 'users.id')
                ->where('solutions.problem_id', '>', '0')
                ->select('solutions.id', 'solutions.pass_rate', 'solutions.problem_id', 'solutions.user_id', 'solutions.time', 'solutions.memory', 'solutions.result', 'solutions.language', 'solutions.code_length', 'solutions.created_at', 'users.name')
                ->orderBy('created_at', 'desc')
                ->skip($size * --$page)
                ->take($size)
                ->get();
    }

    public function getSolution(int $id)
    {
        return $this->model
            ->leftJoin('users', 'solutions.user_id', '=', 'users.id')
            ->select('solutions.id', 'solutions.problem_id', 'solutions.user_id', 'solutions.time', 'solutions.memory', 'solutions.result', 'solutions.language', 'solutions.code_length', 'solutions.created_at', 'solutions.pass_rate', 'users.name')
            ->where('solutions.id', $id)
            ->get();
    }

    public function deleteWhereIn(string $param, array $data = [])
    {
        return $this->model->whereIn($param, $data)->delete();
    }

    public function getWhereCount(array $params = []): int
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

    public function getContestStatus(int $userId, int $contestId, array $columns = ['*'])
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('problem_group_id', $contestId)
            ->where('problem_num', '>', '-1')
            ->get($columns);
    }

    //题目组通用排行榜功能
    public function getRankList(int $groupId)
    {
        return $this->model
            ->where('problem_group_id', $groupId)
            ->where('problem_num', '>=', '0')
            ->leftJoin('users', 'users.id', '=', 'solutions.user_id')
            ->select('users.id', 'users.name', 'solutions.result', 'solutions.created_at', 'solutions.pass_rate', 'solutions.problem_num')
            //注意时间的选择标准，judge_time是批量更新的，应该根据创建时间来排序
            ->orderBy('users.id', 'desc')
            ->orderBy('solutions.created_at', 'asc')
            ->get();
    }

    //判断某道题提交错误次数是否与定义次数匹配
    public function getUnPassProblemSolutionCount(int $userId, int $problemId, int $times)
    {
        return $this->model
                ->where('problem_id', $problemId)
                ->where('user_id', $userId)
                ->Where('result', '<>', 4)
                ->orderBy('created_at', 'desc')
                ->take($times)
                ->count() == $times;
    }

    /*
     * 首页会用到的信息展示数据
     */

    public function getTodaySubmits()
    {
        $now = Carbon::now();
        $startTime = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0);

        return $this->model
            ->where('created_at', '<', $now->toDateTimeString())
            ->where('created_at', '>', $startTime->toDateTimeString())
            ->count();
    }

    public function getThisWeekSubmits()
    {
        $now = Carbon::now();
        $startTime = Carbon::now()->startOfWeek();

        return $this->model
            ->where('created_at', '<', $now->toDateTimeString())
            ->where('created_at', '>', $startTime->toDateTimeString())
            ->count();
    }

    public function getThisMonthSubmits()
    {
        $now = Carbon::now();
        $startTime = Carbon::now()->startOfMonth();

        return $this->model
            ->where('created_at', '<', $now->toDateTimeString())
            ->where('created_at', '>', $startTime->toDateTimeString())
            ->count();
    }

    public function getSolutionStatistics(int $days)
    {
        $now = Carbon::now();
        $today = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->toDateString();

        $result = [];

        $todaySubmit = $this->model
            ->where('created_at', '>', $today . ' 00:00:00')
            ->where('created_at', '<', $now->toDateTimeString())
            ->count();
        $todaySolved = $this->model
            ->where('created_at', '>', $today . ' 00:00:00')
            ->where('created_at', '<', $now->toDateTimeString())
            ->where('result', 4)
            ->count();

        array_push($result, [
            'date' => substr($today,5),
            'submit' => $todaySubmit,
            'solved' => $todaySolved
        ]);

        for ($i = 0; $i < $days; $i++) {

            $thatDayStart = Carbon::createFromFormat('Y-m-d', $today)->subDays($i + 1);
            $thatDayEnd = Carbon::createFromFormat('Y-m-d', $today)->subDays($i);
            $submit = $this->model
                ->where('created_at', '<', $thatDayEnd->toDateTimeString())
                ->where('created_at', '>', $thatDayStart->toDateTimeString())
                ->count();
            $solved = $this->model
                ->where('created_at', '<', $thatDayEnd->toDateTimeString())
                ->where('created_at', '>', $thatDayStart->toDateTimeString())
                ->where('result', 4)
                ->count();

            array_push($result, [
                'date' => $thatDayStart->format("m-d"),
                'submit' => $submit,
                'solved' => $solved
            ]);
        }
	
        return $result;
    }
}
