<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:29
 */

namespace NEUQOJ\Services;


use Hamcrest\Util;
use Illuminate\Support\Facades\DB;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\ProblemGroup\ContestEndedException;
use NEUQOJ\Exceptions\ProblemGroup\ContestNotAvailableException;
use NEUQOJ\Exceptions\ProblemGroup\ContestNotExistException;
use NEUQOJ\Exceptions\ProblemGroup\LanguageErrorException;
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

    public function getContest(int $contestId,array $columns = ['*'])
    {
        //使用这个方法前请先检查contest是否存在。
        return $this->problemGroupService->getProblemGroup($contestId,$columns);
    }

    public function getContestIndex(int $userId = -1, int $groupId)
    {
        //检查权限
        if($userId!=-1&&!$this->canUserAccessContest($userId,$groupId))
            throw new NoPermissionException();

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

    public function getContestDetail(int $groupId)
    {
       //用于获取竞赛的所有数据，用于更新
        $contestInfo = $this->problemGroupService->getProblemGroup($groupId,['title','type','description','private','langmask']);

        if($contestInfo == null || $contestInfo->type != 1) throw new ContestNotExistException();

        if($contestInfo->langmask == null) $contestInfo->langmask = 0;

        //根据计算出的掩码值  还原langmask
        $langs = [];

        $lang_count = count($this->problemGroupService->language_ext);

        for($i=0;$i<$lang_count;$i++)
        {
            if ($contestInfo->langmask & (1 << $i))
                $langs[] = $i;
        }

        $contestInfo['langmask'] = $langs;

        //题目信息（竞赛中，只显示id，题号，标题，当前设计题号一但产生不可更改）

        $problemInfo = $this->problemGroupRelationRepo->getBy('problem_group_id',$groupId,['problem_id','problem_num','problem_title']);

        //权限信息，只显示当前加入到竞赛中的用户列表，不再取出密码

        if($contestInfo->private != 0)
            $admissionInfo = $this->problemAdmissionRepo->getBy('problem_group_id',$groupId,['user_id']);

        //组装整个数组

        $data['contest_info'] = $contestInfo;
        $data['problems_info'] = $problemInfo;
        if(isset($admissionInfo))
            $data['user_ids'] = $admissionInfo;

        return $data;
    }

    public function getProblem(int $groupId, int $problemNum)
    {
        $problem =  $this->problemGroupService->getProblemByNum($groupId,$problemNum);

        return $problem;
    }

    public function getAllContests(int $page, int $size)
    {
        $totalCount = $this->problemGroupRepo->getProblemGroupCount(1);

        $groups = $this->problemGroupRepo->paginate($page,$size,
            ['type' => 1],['id','title','creator_id','creator_name','start_time','end_time','private','status']);

        return ['data' => $groups,'total_count' => $totalCount];
    }

    //创建一个竞赛，如果成功，返回新创建的竞赛id，否则返回-1
    public function createContest(array $data,array $problemIds,array $users=[]):int
    {
        //根据私有性类别来创建
        $data['type'] = 1;
        $id = -1;


        //传入的problems数组只包括id,初步组装数据格式

        $problems  = [];

        foreach ($problemIds as $problemId)
        {
            $problems[] = ['problem_id' => $problemId];
        }


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

    public function deleteContest(int $groupId):bool
    {
        if($this->isContestExist($groupId))
            return $this->problemGroupService->deleteProblemGroup($groupId);
        return false;
    }

    public function updateContestInfo(int $groupId,array $data):bool
    {
        $group = $this->problemGroupService->getProblemGroup($groupId,['type','start_time','end_time']);

        if($group==null|| $group->type!=1) throw new ContestNotExistException();

        //检查比赛是否正在进行中，若已经开始，不允许再更改开始时间
        $startTime = strtotime($group->start_time);
        $endTime = strtotime($group->end_time);
        $time = time();
        if($startTime > $time||$time < $endTime)
        {
            if(isset($data['start_time'])) unset($data['start_time']);//直接无效索引
        }

        return $this->problemGroupService->updateProblemGroup($groupId,$data);
    }

    //批量重置竞赛中的题目
    public function updateContestProblem(int $contestId,array $problemIds):bool
    {
        //重新组装题目
        $problems = [];
        foreach ($problemIds as $problemId)
        {
            $problems[] = ['problem_id' => $problemId,'problem_score' => null];
        }

        return $this->problemGroupService->updateProblems($contestId,$problems);
    }

    public function resetContestPassword(int $groupId,string $password):bool
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

    public function resetContestPermission(int $groupId,array $users):bool
    {
        $group = $this->problemGroupRepo->get($groupId,['type','private'])->first();
        //同上
        if($group == null||$group->type!=1||$group->private!=1)
            return false;

        return $this->problemGroupService->resetGroupAdmissions($groupId,$users);
    }

    public function getRankList(int $groupId)
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

    public function searchContest(string $keyword,int $page,int $size)
    {
        $pattern = '%'.$keyword.'%';

        $totalCount = $this->problemGroupRepo->getProblemGroupCountLike(1,$pattern);

        $contests = $this->problemGroupRepo->searchProblemGroup(1,$pattern,$page,$size);

        $data = ['total_count' => $totalCount,'data' => $contests];

        return $data;
    }

    public function getStatus(int $groupId,int $page,int $size,array $conditions=[])
    {
        $data = $this->problemGroupService->getSolutions($groupId,$page,$size,$conditions);
        return ['data' => $data];
    }

    public function isContestExist(int $groupId):bool
    {
        $group = $this->problemGroupRepo->get($groupId,['type'])->first();

        if($group==null||$group->type!=1)
            return false;
        return true;
    }

    public function submitProblem(int $userId,int $groupId,int $problemNum,array $data):int
    {
        //先检测用户能不能提交
        $group = $this->problemGroupRepo->get($groupId,['private','type','langmask','start_time','end_time'])->first();

        //检查时间

        $currentTime = time();

        $startTime = strtotime($group->start_time);
        $endTime = strtotime($group->end_time);

        if($startTime > $currentTime)
            throw new ContestNotAvailableException();
        elseif($currentTime > $endTime)
            throw new ContestEndedException();

        if($group == null || $group->type!=1) throw new NoPermissionException();

        if($group->private != 0)
        {
            $admission = $this->problemAdmissionRepo->getByMult(['user_id' => $userId,'problem_group_id'=>$groupId])->first();
            if($admission == null) throw new NoPermissionException();
        }

        //检查语言
        if(!$this->problemGroupService->checkLang($data['language'],$group->langmask))
            throw new LanguageErrorException();

        //获取题目id
        $relation = $this->problemGroupRelationRepo->getByMult(['problem_group_id'=>$groupId,'problem_num'=>$problemNum],['problem_id'])->first();

        if($relation == null)
            return false;

        $data['problem_group_id'] = $groupId;

        return $this->problemService->submitProblem($relation->problem_id,$data,$relation->problem_num);
    }

    public function isUserContestCreator(int $userId, int $groupId): bool
    {
        return $this->problemGroupService->isUserGroupCreator($userId,$groupId);
    }

    public function canUserAccessContest(int $userId, int $groupId): bool
    {

        $group = $this->problemGroupRepo->get($groupId,['private','type','start_time','creator_id'])->first();

        //如果是创建者 直接可以获得权限，管理员也应该一样
        if($userId = $group->creator_id) return true;
        //TODO: 管理员权限检查

        //判断时间
        $currentTime = time();

        $startTime = strtotime($group->start_time);

        //尚未开始的比赛
        if($startTime > $currentTime)
            throw new ContestNotAvailableException();

        if($group == null || $group->type!=1)//判断题目组类型
            return false;

        if($group->private == 0)
            return true;

        $admission = $this->problemAdmissionRepo->getByMult(['user_id' => $userId,'problem_group_id'=>$groupId])->first();

        return !($admission==null);
    }

    public function getInContestByPassword(int $userId, int $groupId, string $password): bool
    {
        $group = $this->problemGroupRepo->get($groupId,['private','password','type'])->first();

        if($group == null || $group->type!=1 || $group->private!=1) return false;

        $admission = $this->problemAdmissionRepo->getByMult(['user_id' => $userId,'problem_group_id' => $groupId])->first();

        if($admission!=null) return true;//已经有权限了

        if(!Utils::pwCheck($password,$group->password))
            throw new PasswordErrorException();

        return $this->problemAdmissionRepo->insert(['user_id' => $userId,'problem_group_id'=>$groupId]) == 1;
    }
}