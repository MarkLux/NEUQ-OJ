<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/22
 * Time: 上午9:13
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotAvailableException;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Services\Contracts\HomeworkServiceInterface;
use NEUQOJ\Repository\Models\User;
class HomeworkService implements HomeworkServiceInterface
{
    private $problemGroupService;
    private $userGroupService;
    private $problemGroupRelationRepo;
    private $problemService;
    private $problemAdmissionRepo;
    private $problemGroupRepo;
    private $solutionRepo;
    private $cacheService;

    public function __construct(
        ProblemGroupService $problemGroupService,UserGroupService $userGroupService,
        ProblemGroupRelationRepository $problemGroupRelationRepository,ProblemService $problemService,
        ProblemGroupAdmissionRepository $problemGroupAdmissionRepository,
        ProblemGroupRepository $problemGroupRepository,SolutionRepository $solutionRepository,CacheService $cacheService
    )
    {
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->problemGroupService = $problemGroupService;
        $this->problemService = $problemService;
        $this->userGroupService = $userGroupService;
        $this->problemAdmissionRepo =$problemGroupAdmissionRepository;
        $this->problemGroupRepo = $problemGroupRepository;
        $this->solutionRepo = $solutionRepository;
        $this->cacheService = $cacheService;
    }

    public function getProblem(int $groupId, int $problemNum)
    {
        $problem =  $this->problemGroupService->getProblemByNum($groupId,$problemNum);

        return $problem;
    }

    public function getAllHomework(int $page,int $size)
    {
        $totalCount = $this->problemGroupRepo->getProblemGroupCount(2);

        $groups = $this->problemGroupRepo->paginate($page,$size,
            ['type' => 2],['id','title','creator_id','creator_name','start_time','end_time','private','status']);

        return ['data' => $groups,'total_count' => $totalCount];
    }
    public function getHomework(int $id, array $columns = ['*'])
    {
        //为了判断类型，必须要加入一个'type'字段
        if($columns!=['*'])
            $columns[] = 'type';

        $homework = $this->problemGroupService->getProblemGroup($id,$columns);

        if($homework == null|| $homework->type!=2)
            throw new HomeworkNotExistException();

        return $homework;
    }

    public function getHomeworkBy(string $param, string $value, array $columns = ['*'])
    {
        if($columns!=['*'])
            $columns[] = 'type';

        $homework = $this->problemGroupService->getProblemGroupBy($param,$value,$columns)->first();

        if($homework == null|| $homework->type!=2)
            throw new HomeworkNotExistException();

        return $homework;
    }

    //获取一个用户组（班级）内的全部作业列表，考虑到规模暂时没有做分页。
    public function getHomeworksInGroup(int $groupId)
    {
        $columns = ['id','title','start_time','end_time','status',''];
        $homeworks = $this->problemGroupService->getProblemGroupBy('user_group_id',$groupId,$columns);
        return $homeworks;
    }


    public function getHomeworkIndex(int $userId = -1, int $homeworkId)
    {
        //没有传用户ID的话，无法查看作业
        if($userId == -1)
            throw new NoPermissionException();

        //检查权限
        if(!$this->canUserAccessHomework($userId,$homeworkId))
            throw new NoPermissionException();

        //获取基本信息
        $homework = $this->problemGroupService->getProblemGroup($homeworkId,[
            'id','title','description','start_time','end_time',
            'creator_id','creator_name', 'status','langmask'
        ]);

        $problemInfo = $this->problemGroupRelationRepo->getProblemInfoInGroup($homeworkId);

        $problemIds = [];

        //消除null值
        foreach ($problemInfo as &$info)
        {
            if($info->submit == null) $info->submit = 0;
            if($info->accepted == null) $info->accepted = 0;
            $problemIds[] = $info->pid;
        }

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
        $data['contest_info'] = $homework;
        $data['problem_info'] = $problemInfo;

        return $data;
    }

    public function addHomework(User $user, int $userGroupId, array $data, array $problems):int
    {
        $data['type'] = 2;
        $id = -1;

        //传入的problems数组只包括id,初步组装数据格式
        $problem = [];
        foreach ($problems as $problemId)
        {
            $problem[] = ['problem_id'=> $problemId];
        }

        $id = $this->problemGroupService->createProblemGroup($data,$problem);

        return $id;
    }
    public function updateHomeworkInfo(int $homeworkId, array $data):bool
    {
        $group = $this->problemGroupService->getProblemGroup($homeworkId,['type','start_time','end_time']);

        if($group==null|| $group->type!=2) throw new HomeworkNotExistException();

        //检查比赛是否正在进行中，若已经开始，不允许再更改开始时间
        $startTime = strtotime($group->start_time);
        $endTime = strtotime($group->end_time);
        $time = time();
        if($startTime > $time||$time < $endTime)
        {
            if(isset($data['start_time'])) unset($data['start_time']);//直接无效索引
        }

        return $this->problemGroupService->updateProblemGroup($homeworkId,$data);
    }
    public function updateHomeworkProblem(int $homeworkId, array $problems):bool
    {
        //重新组装题目
        $problemData = [];
        foreach ($problems as $problem)
        {
            $problemData[] = ['problem_id' => $problem->id,'problem_score' => $problem->sorce];
        }

        return $this->problemGroupService->updateProblems($homeworkId,$problems);
    }
    public function deleteHomework(User $user, int $homeworkId):bool
    {
        $flag =false;
        if($this->isHomeworkExist($homeworkId))
           DB::transaction(function ()use($homeworkId,&$flag)
           {
               $this->problemGroupRelationRepo->deleteWhere(['problem_group_id'=>$homeworkId]);
               $this->problemGroupRepo->deleteWhere(['id'=>$homeworkId]);
               $this->solutionRepo->deleteWhere(['problem_group_id'=>$homeworkId]);
               $flag = true;
           });

        return $flag;
    }

    public function isHomeworkExist(int $homeworkId): bool
    {
        $homework = $this->problemGroupService->getProblemGroup($homeworkId,['type']);

        if($homework == null||$homework->type != 2) return false;

        return true;
    }
    public function isUserHomeworkOwner(int $userId, int $homeworkId):bool
    {
        return $this->problemGroupService->isUserGroupCreator($userId,$homeworkId);
    }
    public function canUserAccessHomework(int $userId, int $homeworkId):bool
    {
        $group = $this->problemGroupRepo->get($homeworkId,['user_group_id','type','start_time','creator_id'])->first();
        //如果是创建者 直接可以获得权限，管理员也应该一样
        if($userId = $group->creator_id) return true;
        //TODO: 管理员权限检查

        //判断是否为作业
        if ($group == null || $group->type !=2)
            return false;

        //判断用户是否在该组里
        if(!($this->userGroupService->isUserInGroup($userId,$group->user_group_id)))
            return false;
        //判断时间
        $currentTime = time();

        $startTime = strtotime($group->start_time);

        //尚未开始的比赛
        if($startTime > $currentTime)
            throw new HomeworkNotAvailableException();

        return true;
    }

    public function getHomeworkStatus(int $userId, int $homeworkId)
    {
        $totalCount = $this->problemGroupService->getSolutionCount($homeworkId);
        $data = $this->problemGroupService->getSolutions($homeworkId);

        $group = $this->problemGroupRepo->get($homeworkId,['user_group_id','type','start_time','creator_id'])->first();

        if($userId = $group->creator_id)
            return ['data' => $data,'total_count' => $totalCount]; ;

        $homeworkstatus =[];
        //只是在组里的学生
        foreach ($data as $item)
        {
            if($item['user_id']==$userId)
                array_push($homeworkstatus,$item);
        }
        $totalCount = count($homeworkstatus);
        return ['data'=>$homeworkstatus,'total_count'=>$totalCount];
    }
    public function getHomeworkRank(int $homeworkId)
    {
        $group = $this->problemGroupService->getProblemGroup($homeworkId,['title','type','start_time','end_time','status']);

        if($group == null || $group->type!=2) return false;


        //先检查是否存在缓存

        $cacheKey = 'homework_'.$homeworkId;

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
        $solutions = $this->solutionRepo->getRankList($homeworkId)->toArray();

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
    public function submitProblem(int $userId, int $groupId, int $problemNum, array $data):int
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

        if($group == null || $group->type!=2) throw new NoPermissionException();



        //检查语言
        if(!$this->problemGroupService->checkLang($data['language'],$group->langmask))
            throw new LanguageErrorException();

        //获取题目id
        $relation = $this->problemGroupRelationRepo->getByMult(['problem_group_id'=>$groupId,'problem_num'=>$problemNum],['problem_id'])->first();

        if($relation == null)
            return false;

        $data['problem_group_id'] = $groupId;

        return $this->problemService->submitProblem($relation->problem_id,$data,$relation->problem_id);
    }

}