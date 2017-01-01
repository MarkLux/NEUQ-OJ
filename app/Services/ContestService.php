<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:29
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Services\Contracts\ContestServiceInterface;
use NEUQOJ\Repository\Eloquent\ProblemGroupAdmissionRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;

class ContestService implements ContestServiceInterface
{
    private $problemGroupService;
    private $problemGroupRelationRepo;
    private $problemGroupRepo;
    private $problemAdmissionRepo;
    private $problemService;
    private $solutionRepo;

    public function __construct(
        ProblemGroupService $problemGroupService,ProblemGroupRepository $problemGroupRepository,
        ProblemGroupRelationRepository $problemGroupRelationRepository,ProblemGroupAdmissionRepository $problemGroupAdmissionRepository,
        ProblemService $problemService,SolutionRepository $solutionRepository
    )
    {
        $this->problemGroupRepo = $problemGroupRepository;
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->problemGroupService = $problemGroupService;
        $this->problemAdmissionRepo = $problemGroupAdmissionRepository;
        $this->problemService = $problemService;
        $this->solutionRepo = $solutionRepository;
    }

    function getContest(int $userId = -1, int $groupId)
    {
        if(!$this->canUserAccessContest($userId,$groupId))
            throw new NoPermissionException();

        //获取基本信息

        $group = $this->problemGroupService->getProblemGroup($groupId,['id','title','description','creator_id','creator_name','start_time','end_time','private','status']);

        //获取题目信息

        $problems = $this->problemGroupRelationRepo->getProblemInfosInGroup($groupId)->toArray();

        //刷新提交量和正确数量

        $submitted = $this->solutionRepo->refreshProblemSubmitted(); //有效率问题

        dd($problems);
    }

    function getProblem(int $groupId, int $problemNum)
    {
        // TODO: Implement getProblem() method.
    }

    function getAllContests(int $page, int $size)
    {
        $groups = $this->problemGroupRepo->paginate($page,$size,
            ['type' => 1],['id','title','creator_id','creator_name','start_time','end_time','private','status']);

        return $groups;
    }

    //创建一个竞赛，如果成功，返回新创建的竞赛id，否则返回-1
    function createContest(array $data,array $users=[]):int
    {
        //根据私有性类别来创建
        $data['type'] = 1;
        $id = -1;

        DB::transaction(function()use($data,$users,&$id){
            $id = $this->problemGroupService->createProblemGroup($data);
            //如果是指定可见的私有模式,重新组装数据
            if($data['private'] == 2&&!empty($users))
            {
                $admissions = [];
                foreach ($users as $user){
                    $admissions[] = ['user_id' => $user,'problem_group_id'=>$id];
                }
                $this->problemAdmissionRepo->insert($admissions);
            }
        });

        return $id;
    }

    function deleteContest(int $groupId):bool
    {
        if($this->isContestExist($groupId))
            return $this->problemGroupService->deleteProblemGroup($groupId);
        return false;
    }

    function updateContest(int $groupId,array $data):bool
    {
        if($this->isContestExist($groupId))
            return $this->problemGroupService->updateProblemGroup($groupId,$data);
        return false;
    }

    function resetContestPassword(int $groupId,string $password):bool
    {
        //获取组基本信息
        $group = $this->problemGroupRepo->get($groupId,['type','private'])->first();
        //检测题目组是否是竞赛以及私有性设置是否正确
        if($group == null||$group->type!=1||$group->private!=1)
            return false;
        else
            return $this->problemGroupService->updateProblemGroup($groupId,['password' => md5($password)]);
    }

    function resetContestPermission(int $groupId,array $users):bool
    {
        $group = $this->problemGroupRepo->get($groupId,['type','private'])->first();
        //同上
        if($group == null||$group->type!=1||$group->private!=1)
            return false;

        //TODO: 考虑怎么实现去重
    }

    function getRankList(int $groupId)
    {
        //TODO 使用redis缓存数据
    }

    function searchContest(string $keyword,int $page,int $size)
    {
        $pattern = '%'.$keyword.'%';

        $totalCount = $this->problemGroupRepo->getContestCount($pattern);

        $contests = $this->problemGroupRepo->searchContest($pattern,$page,$size);

        $data = ['total_count' => $totalCount,'contests' => $contests];

        return $data;
    }

    function getStatus(int $groupId)
    {
        //TODO 考虑是否使用缓存
    }

    function isContestExist(int $groupId):bool
    {
        $group = $this->problemGroupRepo->get($groupId,['type'])->first();

        if($group==null||$group->type!=1)
            return false;
        return true;
    }

    function submitProblem(int $groupId,int $problemNum,array $data):int
    {
        //先检测用户能不能提交
        if(!$this->canUserAccessContest($data['user_id'],$groupId))
            throw new NoPermissionException();

        $relation = $this->problemGroupRelationRepo->getBy(['problem_group_id'=>$groupId,'problem_num'=>$problemNum],['problem_id'])->first();

        if($relation == null)
            return false;

        $data['problem_group_id'] = $groupId;

        return $this->problemService->submitProblem($relation->problem_id,$data);
    }

    function canUserAccessContest(int $userId, int $groupId): bool
    {
        $group = $this->problemGroupRepo->get($groupId,['private','type'])->first();

        if($group == null || $group->type!=1)//判断题目组类型
            return false;

        if($group->private == 0)
            return true;

        $admission = $this->problemAdmissionRepo->getByMult(['user_id' => $userId,'problem_group_id'=>$groupId])->first();

        return !($admission==null);
    }

    function getInContestByPassword(int $userId, int $groupId, string $password): bool
    {
        $group = $this->problemGroupRepo->get($groupId,['private'])->first();

        if($group == null || $group->private!=1) return false;

        $admission = $this->problemAdmissionRepo->getByMult(['user_id' => $userId,'problem_group_id' => $groupId])->first();

        if($admission!=null) return true;//已经有权限了

        return $this->problemAdmissionRepo->insert(['user_id' => $userId,'problem_group_id'=>$groupId]) == 1;
    }
}