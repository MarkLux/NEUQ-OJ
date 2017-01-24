<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/22
 * Time: 上午9:13
 */

namespace NEUQOJ\Services;


use League\Flysystem\Exception;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Services\Contracts\HomeworkServiceInterface;

class HomeworkService implements HomeworkServiceInterface
{
    private $problemGroupService;
    private $userGroupService;
    private $problemGroupRelationRepo;
    private $problemService;

    public function __construct(
        ProblemGroupService $problemGroupService,UserGroupService $userGroupService,
        ProblemGroupRelationRepository $problemGroupRelationRepository,ProblemService $problemService
    )
    {
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
        $this->problemGroupService = $problemGroupService;
        $this->problemService = $problemService;
        $this->userGroupService = $userGroupService;
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

    //用户获取作业的基本面板，
    public function getHomeworkIndex(int $userId = -1, int $homeworkId)
    {

    }

    public function isHomeworkExist(int $homeworkId): bool
    {
        $homework = $this->problemGroupService->getProblemGroup($homeworkId,['type']);

        if($homework == null||$homework->type != 2) return false;

        return true;
    }
}