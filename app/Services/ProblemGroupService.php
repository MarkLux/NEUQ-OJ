<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:20
 */

namespace NEUQOJ\Services;


use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Repository\Eloquent\ConfigRepository;
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
    public $language_ext=["c", "cc", "pas", "java", "rb", "sh", "py", "php","pl", "cs","m","bas","scm","c","cc","lua","js"];


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

    //注意可能是多条
    public function getProblemGroupBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->problemGroupRepo->getBy($param,$value,$columns);
    }

    public function getProblemByNum(int $groupId, int $problemNum)
    {
        return $this->problemGroupRelationRepo->getProblemByNum($groupId,$problemNum)->first();
    }

    public function createProblemGroup(array $data,array $problems=[]): int
    {
        $id = -1;
        $flag = false;

        //计算语言掩码
        $data['langmask'] = $this->getLangMask($data['langmask']);
        //$problems数组传入时只存放有problem_id

        //合成题目id
        $problemIds = [];

        foreach ($problems as $problem)
            $problemIds[] = $problem['problem_id'];


        DB::transaction(function()use($data,$problems,$problemIds,&$id,&$flag){

            $confirmProblemIds = $this->problemRepo->getIn('id',$problemIds,['id'])->toArray();
            //检测是否有不存在的题目id
            if(count($confirmProblemIds)!=count($problemIds)) throw new ProblemNotExistException();

            $id = $this->problemGroupRepo->insertWithId($data);

            $i=0;//题号从0开始

            //重新填充数据
            foreach ($problems as &$problem){
                $problem['problem_group_id'] = $id;
                $problem['problem_num'] = $i++;
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
        $problemGroup = $this->problemGroupRepo->get($groupId,['id'])->first();

        return !($problemGroup == null);
    }

    public function updateProblems(int $groupId,array $problems):bool
    {
        //更新整体的题目关系，problems数组要求是多维数组,带有title,如果没有score的话加上设定为null或者0，不能没有该索引
        //整体上看虽然逻辑比较清晰但是感觉设计不太合理，效率低

        if(!$this->isProblemGroupExist($groupId)) return false;

        $problemIds = [];
        $i = 0;

        //抽取出所有的题目id
        foreach($problems as &$problem)
        {
            $problemIds[] = $problem['problem_id'];
            $problem['problem_group_id'] = $groupId;
            $problem['problem_num'] = $i++;
        }

        //排序可能会出bug，因为没有orderBy
        $confirmProblemIds = $this->problemRepo->getIn('id',$problemIds,['id'])->toArray();

        if(count($confirmProblemIds)!=count($problemIds))//有 不存在的题目
            throw new ProblemNotExistException();

        DB::transaction(function()use($groupId,$problems){
            //先把原来的solution中的num全部标记为-1（相当于删除）
            $this->solutionRepo->updateWhere(['problem_group_id' => $groupId],['problem_num' => -1]);

            //删除原关系表
            $this->problemGroupRelationRepo->deleteWhere(['problem_group_id' => $groupId]);

            //重新插入新关系表
            $this->problemGroupRelationRepo->insert($problems);

            //恢复没有失效的题解（有效率问题）
            foreach ($problems as $problem)
            {
                $this->problemGroupRelationRepo->updateWhere(['problem_group_id'=>$groupId,'problem_id'=>$problem['problem_id']],
                    ['problem_num'=>$problem['problem_num'],'problem_score'=>$problem['problem_score']]);
            }
        });

        return true;

    }

    public function getSolutionCount(int $groupId): int
    {
        return $this->solutionRepo->getWhereCount(['problem_group_id' => $groupId]);
    }

    public function getSolutions(int $groupId,int $page=1,int $size=15,array $conditions=[])
    {
        $conditions['problem_group_id'] = $groupId;
        return $this->solutionRepo->paginate($page,$size,$conditions);
    }

    public function isUserGroupCreator(int $userId,int $groupId): bool
    {
       $group = $this->getProblemGroup($groupId,['creator_id']);

       if($group == null ||$group->creator_id != $userId) return false;
       return true;
    }

    public function getGroupAdmissions(int $groupId)
    {
        $admissions = $this->admissionRepo->getBy('problem_group_id',$groupId,['problem_group_id','user_id'])->toArray();

        return $admissions;
    }

    public function resetGroupAdmissions(int $groupId, array $newData): bool
    {
        if(!$this->isProblemGroupExist($groupId)) return false;//不存在

        $flag = false;

        //先把之前的权限全部删除了，然后再重新插入一次（感觉很蠢）
        DB::transaction(function()use($groupId,$newData,&$flag){
            $this->admissionRepo->deleteWhere(['problem_group_id' => $groupId]);
            $this->admissionRepo->insert($newData);
            $flag = true;
        });

        return $flag;
    }

    /**
     * 用于检查语言选择
     */

    private function getLangMask(array $language):int
    {
        //原版配置项,注意原oj上lang_ext数量比这个少，算出来的langmask不一样
        //所以不再兼容老oj版本了

        $langmask=0;
        foreach($language as $t){
            $langmask+=1<<$t;
        }
        $langmask=((1<<count($this->language_ext))-1)&(~$langmask);

        return $langmask;
    }

    public function checkLang(int $langCode,int $langmask):bool
    {
        //检查语言源码 其实还可以设置oj全局语言源码来禁用语言，以后考虑添加上
        $langCount = count($this->language_ext);
        $lang=(~((int)$langmask))&((1<<($langCount))-1);
        return $lang&(1<<$langCode);
    }
}