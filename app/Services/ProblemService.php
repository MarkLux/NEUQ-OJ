<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:41
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\Contracts\ProblemServiceInterface;
use Illuminate\Support\Facades\File;
use NEUQOJ\Repository\Eloquent\ProblemRepository;

class ProblemService
{

    private $problemRepo;
    private $solutionRepo;
    private $deletionService;

    private function getPath(int $problemId):string
    {
        return '/home/judge/data/'.$problemId.'/';
    }

    function __construct(
        ProblemRepository $problemRepository,SolutionRepository $solutionRepository,
        DeletionService $deletionService
    )
    {
        $this->problemRepo = $problemRepository;
        $this->solutionRepo = $solutionRepository;
        $this->deletionService = $deletionService;
    }

    /**
     * 添加题目
     */
    function addProblem(User $user,array $problemData,array $testData):int
    {
        $problemData['creator_id'] = $user->id;
        $problemData['creator_name'] = $user->name;

        //数据必须已经经过验证
        $id = $this->problemRepo->insertWithId($problemData);

        //添加一些必要的验证逻辑？

        $path = $this->getPath($id);

        //创建文件目录
        if(!File::makeDirectory($path,  $mode = 0755))
            return -1;

        //4个文件
        File::put($path.'sample.in',$problemData['sample_input']);

        File::put($path.'sample.out',$problemData['sample_output']);

        File::put($path.'test.in', $testData['input']);

        File::put($path.'test.out', $testData['output']);

        return $id;
    }

    /**
     *获取题目以及状态辅助函数
     */

    function getProblemById(int $problemId)
    {
       return $this->problemRepo->get($problemId)->first();
    }

    function getProblemBy(string $param, $value)
    {
        return $this->problemRepo->getBy($param,$value)->first();
    }

    function getProblemByMult(array $condition)
    {
        return $this->problemRepo->getByMult($condition)->first();
    }

    function isProblemExist(int $problemId): bool
    {
        return $this->problemRepo->get($problemId)->first()!=null;
    }

    /**
     * 修改题目信息
     */

    function updateProblem(int $problemId, array $problemData,array $testData):bool
    {
        //数据必须经过过滤 默认不更新testData（耗时）
        if($this->problemRepo->update($problemData,$problemId)!=1)
            return false;

        //更新输入输出测试数据
        $path = $this->getPath($problemId);

        if(File::isDirectory($path))
        {
            File::put($path.'sample.in',$problemData['sample_input']);

            File::put($path.'sample.out',$problemData['sample_output']);

            File::put($path.'test.in', $testData['input']);

            File::put($path.'test.out', $testData['output']);

            return true;
        }

        return false;
    }

    /**
     * 删除题目（软删除并加入日志）
     */

    function deleteProblem(User $user,int $problemId): bool
    {
        //TODO: 删除一道题目会涉及到很多其他的表，随着以后系统的扩充慢慢完善这个方法的内容

    }

    function searchProblemsCount(string $likeName): int
    {
        // TODO: Implement searchProblemsCount() method.
    }

    function searchProblems(string $likeName, int $start, int $size)
    {
        // TODO: Implement searchProblems() method.
    }
}