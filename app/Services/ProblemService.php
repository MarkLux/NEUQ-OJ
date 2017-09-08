<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:41
 */

namespace NEUQOJ\Services;


use Carbon\Carbon;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemTagRelationRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;
use NEUQOJ\Repository\Models\User;
use Illuminate\Support\Facades\File;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use Illuminate\Support\Facades\DB;

class ProblemService
{

    private $problemRepo;
    private $solutionRepo;
    private $sourceRepo;
    private $tagRelationRepo;
    private $problemGroupRelationRepo;
    private $converter;
    private $judgeService;

    //获取对应题目数据的磁盘储存路径

    private function getPath(int $problemId): string
    {
        return Utils::getProblemDataPath($problemId);
    }

    public function __construct(
        ProblemRepository $problemRepository, SolutionRepository $solutionRepository,
        SourceCodeRepository $sourceCodeRepository,
        ProblemTagRelationRepository $tagRelationRepository, ProblemGroupRelationRepository $problemGroupRelationRepository,
        JudgeService $judgeService
    )
    {
        $this->problemRepo = $problemRepository;
        $this->solutionRepo = $solutionRepository;
        $this->sourceRepo = $sourceCodeRepository;
        $this->tagRelationRepo = $tagRelationRepository;
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->judgeService = $judgeService;
        $this->converter = app('CommonMarkService');
    }

    /**
     * 添加题目
     */
    public function addProblem(User $user, array $problemData, array $testData)
    {
        $problemData['creator_id'] = $user->id;
        $problemData['creator_name'] = $user->name;

        //数据必须已经经过验证
        $id = $this->problemRepo->insertWithId($problemData);

        //添加一些必要的验证逻辑？

        //转换markdown内容
        $problemData['description'] = $this->converter->convertToHtml($problemData['description']);
        $problemData['input'] = $this->converter->convertToHtml($problemData['input']);
        $problemData['output'] = $this->converter->convertToHtml($problemData['output']);

        $path = $this->getPath($id);

        //创建文件目录
        if (!File::makeDirectory($path, $mode = 0755))
            return -1;

        //多个文件
        File::put($path . '/sample.in', $problemData['sample_input']);

        File::put($path . '/sample.out', $problemData['sample_output']);

        for ($i = 0; $i < count($testData); $i++) {
            File::put($path . '/' . $i . '.in', $testData[$i]['input']);

            File::put($path . '/' . $i . '.out', $testData[$i]['output']);
        }

        $data = $this->judgeService->rsyncTestCase($id);
        $data['id'] = $id;

        return $data;
    }

    /**
     *获取题目以及状态辅助函数
     */

    public function canUserAccessProblem(int $userId, int $problemId): bool
    {
        if (Permission::checkPermission($userId, ['access-any-problem'])) {
            return true;
        }

        $problem = $this->problemRepo->get($problemId, ['is_public', 'creator_id'])->first();
        if ($problem == null) return false;
        if ($problem->is_public == 0 && $problem->creator_id != $userId) return false;
        return true;
    }

    public function getTotalPublicCount(): int
    {
        return $this->problemRepo->getTotalPublicCount();
    }

    public function getTotalCount(): int
    {
        return $this->problemRepo->getTotalCount();
    }

    public function getProblems(int $userId = -1, int $page, int $size)
    {
        if (Permission::checkPermission($userId, ['access-any-problem'])) {
            $problems = $this->problemRepo->getProblemsByAdmin($page, $size)->toArray();
        } else
            $problems = $this->problemRepo->getProblems($page, $size)->toArray();

        //重新组织数组形式
        $data = [];
        $problemIds = [];

        $singleProblem = $problems[0];
        $problemIds[] = $problems[0]['id'];

        $tags = [];
        if ($problems[0]['tag_id'] != null)
            $tags[] = ['tag_title' => $problems[0]['name'], 'tag_id' => $problems[0]['tag_id']];

        if (count($problems) > 1) {
            for ($i = 1; $i < count($problems); $i++) {
                if ($singleProblem['id'] == $problems[$i]['id']) {
                    if ($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['name'], 'tag_id' => $problems[$i]['tag_id']];
                } else {
                    $singleProblem['tags'] = $tags;
                    unset($singleProblem['tag_id']);
                    unset($singleProblem['tag_title']);
                    $data[] = $singleProblem;
                    $singleProblem = $problems[$i];
                    $tags = [];
                    if ($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['name'], 'tag_id' => $problems[$i]['tag_id']];
                }

                $problemIds[] = $problems[$i]['id'];
            }
        }

        //剩下最后一个题目
        $singleProblem['tags'] = $tags;
        unset($singleProblem['tag_id']);
        unset($singleProblem['tag_title']);
        $data[] = $singleProblem;

        //组织用户解题情况,原版 groupBy语法

//        if($userId != -1) {
//
//            $acArr = $this->solutionRepo->getUserAcIds($userId,$problemIds)->toArray();
//
//            $subArr = $this->solutionRepo->getUserSubIds($userId,$problemIds)->toArray();
//
//            $acIds = $subIds = [];
//
//            foreach ($acArr as $item) $acIds[$item['problem_id']] = true;
//            foreach ($subArr as $item) $subIds[$item['problem_id']] = true;
//
//            foreach ($data as &$problem) {
//                if (isset($subIds[$problem['id']]))
//                {
//                    if(isset($acIds[$problem['id']]))
//                        $problem['user_status'] = 'Y';
//                    else
//                        $problem['user_status'] = 'N';
//                }
//                else $problem['user_status'] = null;
//            }
//        }

        if ($userId != -1) {
            $problemIds = [];

            foreach ($data as $problem) {
                $problemIds[] = $problem['id'];
            }

            $userStatuses = $this->solutionRepo->getSolutionsIn('user_id', $userId, 'problem_id', $problemIds, ['problem_id', 'result'])->toArray();

            $subIds = $acIds = [];

            foreach ($userStatuses as $userStatus) {
                $subIds[$userStatus['problem_id']] = true;
                if ($userStatus['result'] == 4) $acIds[$userStatus['problem_id']] = true;
            }

            foreach ($data as &$problem) {
                if (isset($subIds[$problem['id']])) {
                    if (isset($acIds[$problem['id']]))
                        $problem['user_status'] = 'Y';
                    else
                        $problem['user_status'] = 'N';
                } else $problem['user_status'] = null;
            }
        }

        return $data;
    }

    public function getProblemByCreatorId(int $userId, int $page, int $size, array $columns = ['*'])
    {
        $count = $this->problemRepo->getWhereCount(['creator_id' => $userId]);

        $problems = null;

        if ($count > 0) {
            $problems = $this->problemRepo->paginate($page, $size, ['creator_id' => $userId], $columns);
        }

        return [
            'total_count' => $count,
            'problems' => $problems
        ];
    }

    //组织数据 转化md为markdown

    public function getProblemById(int $problemId, array $columns = ['*'])
    {
        return $this->problemRepo->get($problemId, $columns)->first();
    }

    public function getProblemIndex(int $problemId)
    {
        //join过的表不能再简单的用原表主键找   
        $problems = $this->problemRepo->getBy('problems.id', $problemId)->toArray();
        //拿到的全部的数据

        if (count($problems) == 0)
            return false;

        //重新组装
        $data = $problems[0];
        $data['tags'] = [];

        if (count($problems) > 1) {
            foreach ($problems as $problem)
                $data['tags'][] = [
                    'tag_id' => $problem['tag_id'],
                    'tag_title' => $problem['name']
                ];

        } else {
            if ($data['tag_id'] != null)
                $data['tags'][] = [
                    'tag_id' => $data['tag_id'],
                    'tag_title' => $data['name']
                ];
        }
        unset($data['tag_id']);
        unset($data['tag_title']);

        return $data;
    }

    public function getProblemBy(string $param, $value)
    {
        $problems = $this->problemRepo->getBy($param, $value)->toArray();
        //拿到的全部的数据

        if (empty($problems))
            return false;

        //重新组装
        $data = $problems[0];
        $data['tags'] = [];

        if (count($problems) > 1) {
            foreach ($problems as $problem)
                $data['tags'][] = [
                    'tag_id' => $problem['tag_id'],
                    'tag_title' => $problem['tag_title']
                ];
        } else {
            if ($data['tag_id'] != null)
                $data['tags'] = [
                    'tag_id' => $data['tag_id'],
                    'tag_title' => $data['tag_title']
                ];
        }

        unset($data['tag_id']);
        unset($data['tag_title']);

        return $data;
    }

    public function getProblemByMult(array $condition, array $columns = ['*'])
    {
        //缺少组装
        return $this->problemRepo->getByMult($condition, $columns)->first()->toArray();
    }

    public function isProblemExist(int $problemId): bool
    {
        return $this->problemRepo->get($problemId, ['id'])->first() != null;
    }

    /**
     * 修改题目信息
     */

    public function updateProblem(int $problemId, array $problemData, array $testData): bool
    {
        //数据必须经过过滤 默认不更新testData（耗时）
        if ($this->problemRepo->update($problemData, $problemId) != 1)
            return false;

        //更新输入输出测试数据
        $path = $this->getPath($problemId);

        if (File::isDirectory($path)) {
            File::put($path . 'sample.in', $problemData['sample_input']);

            File::put($path . 'sample.out', $problemData['sample_output']);

            for ($i = 0; $i < count($testData); $i++) {
                File::put($path . $i . '.in', $testData[$i]['input']);
                File::put($path . $i . '.out', $testData[$i]['output']);
            }

            return true;
        } else
            return false;
    }

    /**
     * 提交题目
     */

    public function submitProblem(int $problemId, array $data, int $problemNum = -1)
    {
        //写入solution和source_code
        //插入顺序必须是先插入source_code获取id然后再给solution不然一定会编译错误。
        //提交成功后返回solution_id否则返回0
        //题目组中的题目插入时附带题目编号，默认-1

        $problem = $this->problemRepo->get($problemId, ['id', 'time_limit', 'memory_limit', 'submit', 'accepted'])->first();

        if ($problem == null) {
            throw new ProblemNotExistException();
        }

        $code = [
            'source' => $data['source_code'],
            'private' => $data['private']
        ];

        $solutionId = 0;

        $solutionData = [
            'problem_id' => $problemId,
            'problem_num' => $problemNum,
            'problem_group_id' => $data['problem_group_id'],
            'user_id' => $data['user_id'],
            'ip' => $data['ip'],
            'language' => $data['language'],
            'result' => 0,
            'code_length' => $data['code_length']
        ];

        //开启事务处理

        DB::transaction(function () use (&$solutionId, $code, $solutionData) {
            $solutionId = $this->sourceRepo->insertWithId($code);
            $solutionData['id'] = $solutionId;
            $this->solutionRepo->insert($solutionData);
        });

        // 开始判题

        if ($solutionId == 0) {
            throw new InnerError("Fail to create solution");
        }

        if ($data['language'] == 2) {
            // 为java增加内存限制
            $problem->memory_limit *= 2;
        }

        $result = $this->judgeService->judge([
            'src' => $data['source_code'],
            'language' => Utils::switchLanguage($data['language']),
            'max_cpu_time' => $problem->time_limit * 1000,
            'max_memory' => $problem->memory_limit * 1024 * 1024,
            'test_case_id' => $problemId
        ]);

        if ($result == null || $result->code == -1 || $result->code == -3) {
            $this->solutionRepo->update(['result' => -1, 'judger' => $result->judgerName, 'judge_time' => Carbon::now()], $solutionId);
            return [
                'result' => -1,
                'data' => isset($result->data) ? $result->data : 'Unknown Error'
            ];
        } else if ($result->code == -2) {
            // 编译错误
            DB::transaction(function () use ($solutionId, $problem, $result) {
                $this->solutionRepo->update(['result' => 2, 'judger' => $result->judgerName, 'judge_time' => Carbon::now()], $solutionId);
                $this->problemRepo->update(['submit' => $problem->submit + 1], $problem->id);
            });
            return [
                'result' => 2,
                'data' => $result->data,
            ];
        } else {
            if (!empty($result->data->UnPassed)) {
                // 有错误
                DB::transaction(function () use ($solutionId, $problem, $result) {
                    $passRate = floatval(count($result->data->Passed) / (count($result->data->Passed) + count($result->data->UnPassed)));
                    $this->solutionRepo->update(['result' => 3, 'judger' => $result->judgerName, 'pass_rate' => $passRate, 'judge_time' => Carbon::now()], $solutionId);
                    $this->problemRepo->update(['submit' => $problem->submit + 1], $problem->id);
                });

                return [
                    'result' => 3,
                    'data' => $result->data
                ];
            } else {
                // AC
                DB::transaction(function () use ($solutionId, $problem, $result) {
                    $passRate = 1.0;
                    $this->solutionRepo->update(['result' => 4, 'judger' => $result->judgerName, 'pass_rate' => $passRate, 'judge_time' => Carbon::now()], $solutionId);
                    $this->problemRepo->update(['submit' => $problem->submit + 1, 'accepted' => $problem->accepted + 1], $problem->id);
                });

                return [
                    'result' => 4,
                    'data' => $result->data
                ];
            }
        }
    }

    /**
     * 删除题目
     */

    public function deleteProblem(User $user, int $problemId): bool
    {
        $flag = false;

        DB::transaction(function () use ($user, $problemId, &$flag) {
            // 删除题目表
            $this->problemRepo->deleteWhere(['id' => $problemId]);

            // 删除tag关系表

            $this->tagRelationRepo->deleteWhere(['problem_id' => $problemId]);

            // 考虑到排行榜组织问题，没有删除竞赛和作业中的记录

            // $this->problemGroupRelationRepo->deleteWhere(['problem_id' => $problemId]);

            // 考虑删不删solution表，应该没必要

            $flag = true;
        });

        $path = $this->getPath($problemId);

        if (File::isDirectory($path))
            return File::deleteDirectory($path);

        return $flag;
    }

    /**
     * 搜索
     */

    public function searchProblemsCount(string $likeName): int
    {
        $pattern = "%" . $likeName . "%";
        return $this->problemRepo->getWhereLikeCount($pattern);
    }

    public function searchProblems(int $userId = -1, string $likeName, int $start, int $size)
    {
        $pattern = "%" . $likeName . "%";

        $problems = $this->problemRepo->getWhereLike($pattern, $start, $size)->toArray();

        //重新组织数组形式
        $data = [];

        $singleProblem = $problems[0];
        $tags = [];
        if ($problems[0]['tag_id'] != null)
            $tags[] = ['tag_title' => $problems[0]['name'], 'tag_id' => $problems[0]['tag_id']];

        if (count($problems) > 1) {
            for ($i = 1; $i < count($problems); $i++) {
                if ($singleProblem['id'] == $problems[$i]['id']) {
                    if ($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['name'], 'tag_id' => $problems[$i]['tag_id']];
                } else {
                    $singleProblem['tags'] = $tags;
                    unset($singleProblem['tag_id']);
                    unset($singleProblem['tag_title']);
                    $data[] = $singleProblem;
                    $singleProblem = $problems[$i];
                    $tags = [];
                    if ($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['name'], 'tag_id' => $problems[$i]['tag_id']];
                }
            }
        }

        //剩下最后一个题目
        $singleProblem['tags'] = $tags;
        unset($singleProblem['tag_id']);
        unset($singleProblem['tag_title']);
        $data[] = $singleProblem;

        //组织用户解题情况

        if ($userId != -1) {
            $problemIds = [];

            foreach ($data as $problem) {
                $problemIds[] = $problem['id'];
            }

            $userStatuses = $this->solutionRepo->getSolutionsIn('user_id', $userId, 'problem_id', $problemIds, ['problem_id', 'result'])->toArray();

            $subIds = $acIds = [];

            foreach ($userStatuses as $userStatus) {
                $subIds[$userStatus['problem_id']] = true;
                if ($userStatus['result'] == 4) $acIds[$userStatus['problem_id']] = true;
            }

            foreach ($data as &$problem) {
                if (isset($subIds[$problem['id']])) {
                    if (isset($acIds[$problem['id']]))
                        $problem['user_status'] = 'Y';
                    else
                        $problem['user_status'] = 'N';
                } else $problem['user_status'] = null;
            }
        }

        return $data;
    }

    /**
     * 以文件形式获取题解数据
     */

    public function getRunDataPath(int $problemId, string $name)
    {
        $path = $this->getPath($problemId);

        if (File::isDirectory($path)) {
            return $path . $name;
        }

        return null;
    }
}