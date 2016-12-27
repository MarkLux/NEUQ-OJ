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
        $flag = false;

        DB::transaction(function()use($data,$problems,&$flag){
            $this->problemGroupRepo->insert($data);
            $this->problemGroupRelationRepo->insert($problems);
            $flag = true;
        });

        return $flag;
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

    public function addProblem(int $groupId, int $problemId,int $score=null): bool
    {
        //判断数据合理性
        $group = $this->problemGroupRepo->get($groupId,['problem_count'])->first();
        $problem = $this->problemRepo->get($problemId,['problem_id'])->first();
        if($group == null||$problem == null) return false;

        //维护题目数量的字段
        $count = $group->problem_count+1;
        $relation = ['problem_group_id'=>$groupId,'problem_id'=>$problemId,'problem_num'=>$count];
        if($score!=null) $relation['problem_score'] = $score;

        $flag = false;

        DB::transaction(function()use($relation,&$flag){
            $this->problemGroupRelationRepo->insert($relation);
            //更新题号
            $this->problemGroupRepo->update(['problem_count'=>$relation['problem_num']],$relation['problem_group_id']);

            $flag = true;
        });

        return $flag;
    }

    public function removeProblem(int $groupId, int $problemNum): bool
    {
        //判断数据合理性
        $group = $this->problemGroupRepo->get($groupId,['problem_count'])->first();
        $problemId= $this->problemGroupRelationRepo->getByMult(['problem_group_id'=>$groupId,'problem_num'=>$problemNum],['problem_id'])->first()->problem_id;
        if($group==null||$problemId == null) return false;

        $flag = false;
        $solutionIds = $this->solutionRepo->getByMult(['problem_group_id'=>$groupId,'problem_id'=>$problemId],['id'])->toArray();

        DB::transaction(function()use($groupId,$problemId,&$flag,$solutionIds){
            $this->problemGroupRelationRepo->deleteWhere(['problem_group_id'=>$groupId,'problem_id'=>$problemId]);
            $this->solutionRepo->deleteWhereIn('id',$solutionIds);
            $this->sourceRepo->deleteWhereIn('solution_id',$solutionIds);
            //删除相关的所有数据
            $flag = true;
        });
    }

}