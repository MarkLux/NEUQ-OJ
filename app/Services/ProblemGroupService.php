<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:20
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\ProblemGroupAdmissionRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;
use NEUQOJ\Services\Contracts\ProblemGroupServiceInterface;
use Illuminate\Support\Facades\DB;

class ProblemGroupService implements ProblemGroupServiceInterface
{
    private $problemGroupRepo;
    private $problemGroupRelationRepo;
    private $problemRepo;
    private $admissionRepo;
    private $solutionRepo;
    private $sourceRepo;
    private $deletionService;

    public function __construct(
        ProblemGroupRepository $problemGroupRepository, ProblemGroupAdmissionRepository $admissionRepository,
        DeletionService $deletionService,ProblemGroupRelationRepository $problemGroupRelationRepository,
        SolutionRepository $solutionRepository,ProblemRepository $problemRepository,SourceCodeRepository $sourceCodeRepository
    )
    {
        $this->admissionRepo = $admissionRepository;
        $this->problemGroupRepo = $problemGroupRepository;
        $this->solutionRepo = $solutionRepository;
        $this->sourceRepo = $sourceCodeRepository;
        $this->deletionService = $deletionService;
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->problemRepo = $problemRepository;
    }

    public function getProblemGroup(int $groupId, array $columns = ['*'])
    {
        return $this->problemGroupRepo->get($groupId,$columns)->first();
    }

    public function getProblemGroupBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->problemGroupRepo->getBy($param,$value,$columns);
    }

    public function createProblemGroup(array $data,array $problems=[]): int
    {
        $id = -1;
        $flag = false;

        DB::transaction(function()use($data,$problems,&$id,&$flag){
            $id = $this->problemGroupRepo->insertWithId($data);
            //重新填充数据
            foreach ($problems as &$problem){
                $problem['problem_group_id'] = $id;
            }

            $this->problemGroupRelationRepo->insert($problems);
            $flag = true;
        });
        if($flag)
            return $id;
        else
            return -1;
    }

    public function deleteProblemGroup(int $groupId): bool
    {
        $flag = false;
        //开启事务处理
        DB::transaction(function()use($groupId,&$flag){
            //删除三个表中的内容
            $this->problemGroupRepo->deleteWhere(['id' => $groupId]);
            $this->problemGroupRelationRepo->deleteWhere(['problem_group_id'=>$groupId]);
            $this->admissionRepo->deleteWhere(['problem_group_id' => $groupId]);
            $this->solutionRepo->deleteWhere(['problem_group_id' => $groupId]);
            $flag = true;
        });

        return $flag;
    }

    public function updateProblemGroup(int $groupId, array $data): bool
    {
        return $this->problemGroupRepo->update($data,$groupId) == 1;
    }

    public function isProblemGroupExist(int $groupId): bool
    {
        $problemGroup = $this->problemGroupRepo->get($groupId,['id']);

        return !($problemGroup == null);
    }

    //支持多个题目的添加 若存在题号不存在的题目将会返回错误,题目的特定信息应该提前组织在problems数组里
    public function addProblem(int $groupId,array $problems): bool
    {
        $problemIds = [];
        //重新组织数据
        foreach ($problems as $problem)
        {
            $problemIds[] = $problem['problem_id'];
        }
        //判断数据合理性
        $group = $this->problemGroupRepo->get($groupId,['problem_count'])->first();
        $problemIds = $this->problemRepo->getIn('id',$problemIds,['problem_id'])->toArray();
        if($group == null) return false;
        if(count($problemIds)!=count($problems)) return false;//存在题号不存在的题目

        //组装relations
        $count = $group->problem_count;

        foreach ($problems as $problem)
        {
            $problem['problem_group_id'] = $groupId;
            $problem['problem_num'] = ++$count;
        }

        $flag = false;

        DB::transaction(function()use($problems,&$flag,$count,$groupId){
            $this->problemGroupRelationRepo->insert($problems);
            //更新题号
            $this->problemGroupRepo->update(['problem_count'=>$count],$groupId);

            $flag = true;
        });

        return $flag;
    }

    //支持多个题目的删除，如果题号不存在则自动忽略
    public function removeProblem(int $groupId, array $problemNums): bool
    {
        //判断数据合理性
        $group = $this->problemGroupRepo->get($groupId,['problem_count'])->first();
        $relationIds= $this->problemGroupRelationRepo->getRelationsByNums($groupId,$problemNums,['id','problem_id'])->toArray();
        if($group==null||empty($relationIds)) return false;

        $problemIds = [];

        //重新组装数据
        foreach ($relationIds as &$relation)
        {
            $problemIds[] = $relation['problem_id'];
            unset($relation['problem_id']);
        }

        $flag = false;
        $solutionIds = $this->solutionRepo->getSolutionsIn($groupId,$problemIds)->toArray();

        DB::transaction(function()use($groupId,$problemIds,&$flag,$solutionIds,$relationIds){
            $this->problemGroupRelationRepo->deleteWhereIn('id',$relationIds);
            $this->solutionRepo->deleteWhereIn('id',$solutionIds);
            $this->sourceRepo->deleteWhereIn('solution_id',$solutionIds);
            //删除相关的所有数据
            $flag = true;
        });
    }

    public function getSolutionCount(int $groupId): int
    {
        return $this->solutionRepo->getWhereCount(['problem_group_id' => $groupId]);
    }

    public function getSolutions(int $groupId,int $page, int $size)
    {
        return $this->solutionRepo->paginate($page,$size,['problem_group_id'=>$groupId]);
    }

}