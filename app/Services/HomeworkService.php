<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/22
 * Time: 上午9:13
 */

namespace NEUQOJ\Services;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\ProblemGroup\LanguageErrorException;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotAvailableException;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;
use NEUQOJ\Services\Contracts\HomeworkServiceInterface;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Repository\Eloquent\ProblemGroupAdmissionRepository;

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
    private $userGroupRelationRepo;

    public function __construct(
        ProblemGroupService $problemGroupService,UserGroupService $userGroupService,
        ProblemGroupRelationRepository $problemGroupRelationRepository,ProblemService $problemService,
        ProblemGroupAdmissionRepository $problemGroupAdmissionRepository,
        ProblemGroupRepository $problemGroupRepository,SolutionRepository $solutionRepository,CacheService $cacheService,
        UserGroupRelationRepository $userGroupRelationRepository
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
        $this->userGroupRelationRepo = $userGroupRelationRepository;
    }

    public function getProblem(int $groupId, int $problemNum)
    {
        $problem =  $this->problemGroupService->getProblemByNum($groupId,$problemNum);

        return $problem;
    }

    public function getHomework(int $id, array $columns = ['*'])
    {
        //为了判断类型，必须要加入一个'type'字段
        if($columns!=['*'])
            $columns[] = 'type';

        $homework = $this->problemGroupService->getProblemGroup($id,$columns);

        if($homework == null|| $homework->type != 2)
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

    //获取一个用户组（班级）内的全部作业列表
    public function getHomeworksInGroup(int $groupId,int $page,int $size)
    {
        $columns = ['id','title','end_time','status',''];
        $totalCount = $this->problemGroupRepo->getHomeworkCount($groupId);
        $homeworks = $this->problemGroupRepo->paginate($page,$size,['user_group_id' => $groupId],$columns);
        return ['total_count' => $totalCount,'homeworks' => $homeworks];
    }


    public function getHomeworkIndex(int $userId, int $homeworkId)
    {
        //检查权限
        if(!$this->canUserAccessHomework($userId,$homeworkId))
            throw new NoPermissionException();

        //获取基本信息
        $homework = $this->problemGroupService->getProblemGroup($homeworkId,[
            'id','title','description','end_time',
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
        $data['homework_info'] = $homework;
        $data['problem_info'] = $problemInfo;

        return $data;
    }

    public function addHomework(User $user, int $userGroupId, array $data, array $problems):int
    {
        if(!$this->userGroupService->isUserGroupOwner($user->id,$userGroupId))
            throw new NoPermissionException();

        $data['type'] = 2;
        $data['creator_id'] = $user->id;
        $data['creator_name'] = $user->name;

        $data['start_time'] = Carbon::now();

        //传入的problems数组应该包括id和score
        //同时这里避免userGroupId和start_time带来的混乱，在controller层组织数据时要小心

        $id = $this->problemGroupService->createProblemGroup($data,$problems);

        return $id;
    }


    public function updateHomeworkInfo(int $homeworkId, array $data):bool
    {
        $group = $this->problemGroupService->getProblemGroup($homeworkId,['type','end_time']);

        if($group==null|| $group->type!=2) throw new HomeworkNotExistException();

        return $this->problemGroupService->updateProblemGroup($homeworkId,$data);
    }

    public function updateHomeworkProblem(int $homeworkId, array $problems):bool
    {
        return $this->problemGroupService->updateProblems($homeworkId,$problems);
    }

    public function deleteHomework(int $homeworkId):bool
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

        //判断是否为作业
        if ($group == null || $group->type !=2 || $group->user_group_id == null)
            return false;

        //如果是创建者 直接可以获得权限，管理员也应该一样
        if($userId = $group->creator_id) return true;
        //TODO: 管理员权限检查

        //判断用户是否在该组里
        if(!($this->userGroupService->isUserInGroup($userId,$group->user_group_id)))
            return false;

        //作业没有开始时间，只有结束时间(一旦创建随时都可以看)

        return true;
    }

    public function getHomeworkStatus(int $homeworkId,int $page,int $size,array $conditions=[])
    {
        $data = $this->problemGroupService->getSolutions($homeworkId,$page,$size,$conditions);

        return ['solutions' => $data];
    }


    public function getHomeworkRank(int $homeworkId)
    {
        $group = $this->problemGroupService->getProblemGroup($homeworkId,['title','type','end_time','status','user_group_id']);

        if($group == null || $group->type!=2) return false;

        //先检查是否存在缓存

        $cacheKey = 'homework_'.$homeworkId;

        if($this->cacheService->isCacheExist($cacheKey))
        {
            $ranks = $this->cacheService->getRankCache($cacheKey);
            if(!empty($ranks))
            {
                usort($ranks,function($A,$B){
                    if ($A->score!=$B->score) return $A->score<$B->score;
                    else return $A->solved < $B->solved;
                });
                return $ranks;
            }
        }

        //正常mysql查询方法：
        $solutions = $this->solutionRepo->getRankList($homeworkId)->toArray();
        $userRelations = $this->userGroupRelationRepo->getBy('group_id',$group->user_group_id,['user_id','user_code','user_tag'])->toArray();
        $problemRelations = $this->userGroupRelationRepo->getBy('problem_group_id',$homeworkId,['problem_num','problem_score'])->toArray();

        //补充查询用户名片和题目分数

        $userTags = [];
        $problemScores = [];
        foreach ($userRelations as $userRelation)
        {
            $userTags[$userRelation['user_id']] = ['user_code' => $userRelation['user_code'],'user_tag' => $userRelation['user_tag']];
        }

        foreach ($problemRelations as $problemRelation)
        {
            $problemScores[$problemRelation['problem_num']] = $problemRelation['problem_score'];
        }

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
                    'user_code' => $userTags[$solution['id']]['user_code'],
                    'user_tag' => $userTags[$solution['id']]['user_tag'],
                    'score' => 0,
                    'solved' => 0,
                    'problem_wa_num' => []
                ];

                //判断第一个数据

                if($solution['result'] == 4)
                {
                    $rank[$userCnt]['solved']++;
                    $rank[$userCnt]['score'] += $problemScores[$solution['problem_num']];
                }
                elseif($solution['result'] > 4) //没有ac,我在这里多考虑一下编译中、运行中、等待中的情况 跳过这几种情况
                    $rank[$userCnt]['problem_wa_num'][$solution['problem_num']] = 1;

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

        usort($rank,function($A,$B){
            if ($A['score']!=$B['score']) return $A['score']<$B['score'];
            else return $A['solved']<$B['solved'];
        });
        $this->cacheService->setRankCache($cacheKey,$rank,60);

        return $rank;

    }

    public function submitProblem(int $userId, int $groupId, int $problemNum, array $data):int
    {
        if(!$this->canUserAccessHomework($userId,$groupId)) throw new NoPermissionException();
        //先检测用户能不能提交
        $group = $this->problemGroupRepo->get($groupId,['private','type','langmask','end_time'])->first();

        //检查时间
        $currentTime = time();

        $endTime = strtotime($group->end_time);
        if($currentTime > $endTime)
            throw new HomeworkNotAvailableException();

        if($group == null || $group->type!=2) throw new NoPermissionException();

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

}