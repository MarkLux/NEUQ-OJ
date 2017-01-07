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
    private $cacheService;

    public function __construct(
        ProblemGroupService $problemGroupService,ProblemGroupRepository $problemGroupRepository,
        ProblemGroupRelationRepository $problemGroupRelationRepository,ProblemGroupAdmissionRepository $problemGroupAdmissionRepository,
        ProblemService $problemService,SolutionRepository $solutionRepository,CacheService $cacheService
    )
    {
        $this->problemGroupRepo = $problemGroupRepository;
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->problemGroupService = $problemGroupService;
        $this->problemAdmissionRepo = $problemGroupAdmissionRepository;
        $this->problemService = $problemService;
        $this->solutionRepo = $solutionRepository;
        $this->cacheService = $cacheService;
    }

    function getContest(int $userId = -1, int $groupId)
    {
        //获取基本信息
        $contest = $this->problemGroupService->getProblemGroup($groupId,[
            'id','title','description','start_time','end_time',
            'creator_id','creator_name', 'status','langmask'
        ]);

        $problemInfo = $this->problemGroupRelationRepo->getProblemInfoInGroup($groupId);
        $problemIds = [];

        //消除null值
        foreach ($problemInfo as &$info)
        {
            if($info->submit == null) $info->submit = 0;
            if($info->accepted == null) $info->accepted = 0;
            $problemIds[] = $info->pid;
        }

        //获取用户解题状态

        if($userId != -1)
        {
            $userStatuses = $this->solutionRepo->getSolutionsIn('user_id',$userId,'problem_id',$problemIds,['problem_id','result'])->toArray();
            $status = [];

            foreach ($userStatuses as $userStatus)
            {
                $status[$userStatus['problem_id']] = $userStatus['result'];
            }
            foreach ($problemInfo as &$info) {

                if(isset($status[$info->pid]))
                    $info->user_status = $status[$info->pid]==4?'Y':'N';
                else
                    $info->user_status = null;
            }

        }

        $data['contest_info'] = $contest;
        $data['problem_info'] = $problemInfo;

        return $data;
    }

    function getProblem(int $groupId, int $problemNum)
    {
        $problem =  $this->problemGroupService->getProblemByNum($groupId,$problemNum);

        return $problem;
    }

    function getAllContests(int $page, int $size)
    {
        $totalCount = $this->problemGroupRepo->getProblemGroupCount(1);

        $groups = $this->problemGroupRepo->paginate($page,$size,
            ['type' => 1],['id','title','creator_id','creator_name','start_time','end_time','private','status']);

        return ['data' => $groups,'total_count' => $totalCount];
    }

    //创建一个竞赛，如果成功，返回新创建的竞赛id，否则返回-1
    function createContest(array $data,array $problems,array $users=[]):int
    {
        //根据私有性类别来创建
        $data['type'] = 1;
        $id = -1;

        /**
         * 传入的problems数组应该包含题目id、题目标题、题目编号
         */

        DB::transaction(function()use($data,$problems,$users,&$id){
            $id = $this->problemGroupService->createProblemGroup($data,$problems);
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

        //之前已经通过密码加入的用户不进行处理了
    }

    function resetContestPermission(int $groupId,array $users):bool
    {
        $group = $this->problemGroupRepo->get($groupId,['type','private'])->first();
        //同上
        if($group == null||$group->type!=1||$group->private!=1)
            return false;

        return $this->problemGroupService->resetGroupAdmissions($groupId,$users);
    }

    function getRankList(int $groupId)
    {
        $group = $this->problemGroupService->getProblemGroup($groupId,['title','type','start_time','end_time','status']);

        if($group == null || $group->type!=1) return false;

        //先检查是否存在缓存

        $cacheKey = 'contest_'.$groupId;

        if($this->cacheService->isCacheExist($cacheKey))
        {
            $ranks = $this->cacheService->getRankCache($cacheKey);
            if(!empty($ranks))
            {
                usort($ranks,['NEUQOJ\Common\Utils','s_cmp_obj']);
                return $ranks;
            }
        }

        //正常mysql查询方法：
        $solutions = $this->solutionRepo->getRankList($groupId)->toArray();

        $rank = [];//最终保存总数据的数组
        $userCnt = -1;//计算用户总数
        $userId = -1;

        //组装排行榜
        foreach ($solutions as $solution)
        {
            if($userId != $solution['id'])//新的用户
            {
                //创建一个新的数组
                $rank[++$userCnt] = [
                    'user_id' => $solution['id'],
                    'user_name' => $solution['name'],
                    'time' => 0,
                    'solved' => 0,
                    'problem_wa_num' => [],
                    'problem_ac_sec' => []
                ];

                //判断第一个数据

                if($solution['result'] == 4)
                {
                    $rank[$userCnt]['problem_ac_sec'][$solution['problem_num']] = strtotime($solution['created_at']) - strtotime($group->start_time);
                    $rank[$userCnt]['solved']++;
                }
                elseif($solution['result'] > 4) //没有ac,我在这里多考虑一下编译中、运行中、等待中的情况 跳过这几种情况
                    $rank[$userCnt]['problem_wa_num'][$solution['problem_num']] = 1;

                //刷新总时间，注意所有时间全部以秒级正整数方式保存,错题的罚时只在题目成功ac之后才计算
                if($solution['result'] ==4)
                    $rank[$userCnt]['time'] += (strtotime($solution['created_at'])-strtotime($group->start_time));

                $userId = $solution['id'];//标记用户
            }
            else
            {
                //说明不是一个新的用户，还属于上个用户
                if($solution['result'] == 4)//ac
                {
                    if(!isset($rank[$userCnt]['problem_ac_sec'][$solution['problem_num']]))//之前还没有ac过对应的题目
                    {
                        $rank[$userCnt]['solved'] ++;//解题数目+1
                        $rank[$userCnt]['problem_ac_sec'][$solution['problem_num']]  = strtotime($solution['created_at']) - strtotime($group->start_time);
                        //计算题目总罚时
                        if(isset($rank[$userCnt]['problem_wa_num'][$solution['problem_num']]))
                            $rank[$userCnt]['time'] += 1200*$rank[$userCnt]['problem_wa_num'][$solution['problem_num']];
                    }
                    //如果已经ac过这个题目，不再考虑
                }
                elseif ($solution['result'] > 4)//错误
                {
                    if(isset($rank[$userCnt]['problem_wa_num'][$solution['problem_num']]))
                        $rank[$userCnt]['problem_wa_num'][$solution['problem_num']]++;
                    else
                        $rank[$userCnt]['problem_wa_num'][$solution['problem_num']] = 1;
                    //是否应该判断题目已经ac，如果ac了可以考虑不再增加错误了（虽然对罚时没有影响）
                }
            }
        }

        usort($rank,['NEUQOJ\Common\Utils','s_cmp_array']);
        $this->cacheService->setRankCache($cacheKey,$rank,60);

        return $rank;
    }

    function searchContest(string $keyword,int $page,int $size)
    {
        $pattern = '%'.$keyword.'%';

        $totalCount = $this->problemGroupRepo->getProblemGroupCountLike(1,$pattern);

        $contests = $this->problemGroupRepo->searchProblemGroup(1,$pattern,$page,$size);

        $data = ['total_count' => $totalCount,'data' => $contests];

        return $data;
    }

    function getStatus(int $groupId,int $page,int $size)
    {
        $totalCount = $this->problemGroupService->getSolutionCount($groupId);
        $data = $this->problemGroupService->getSolutions($groupId,$page,$size);
        return ['data' => $data,'total_count' => $totalCount];
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

        return $this->problemService->submitProblem($relation->problem_id,$data,$relation->problem_num);
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