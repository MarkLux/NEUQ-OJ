<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:41
 */

namespace NEUQOJ\Services;


use League\CommonMark\CommonMarkConverter;
use NEUQOJ\Repository\Eloquent\ProblemTagRelationRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\Contracts\ProblemServiceInterface;
use Illuminate\Support\Facades\File;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use Illuminate\Support\Facades\DB;

class ProblemService implements ProblemServiceInterface
{

    private $problemRepo;
    private $solutionRepo;
    private $sourceRepo;
    private $deletionService;
    private $tagRelationRepo;
    private $converter;

    //获取对应题目数据的磁盘储存路径

    private function getPath(int $problemId):string
    {
        return '/home/judge/data/'.$problemId.'/';
    }

    public function __construct(
        ProblemRepository $problemRepository,SolutionRepository $solutionRepository,
        DeletionService $deletionService,SourceCodeRepository $sourceCodeRepository,
        ProblemTagRelationRepository $tagRelationRepository
    )
    {
        $this->problemRepo = $problemRepository;
        $this->solutionRepo = $solutionRepository;
        $this->deletionService = $deletionService;
        $this->sourceRepo = $sourceCodeRepository;
        $this->tagRelationRepo = $tagRelationRepository;
        $this->converter = app('CommonMarkService');
    }

    /**
     * 添加题目
     */
    public function addProblem(User $user,array $problemData,array $testData):int
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

    public function canUserAccessProblem(int $userId, int $problemId): bool
    {
        $problem = $this->problemRepo->get($problemId,['is_public','creator_id'])->first();
        if($problem == null) return false;
        if($problem->is_public == 0&&$problem->creator_id != $userId) return false;
        return true;
    }

    public function getTotalPublicCount():int
    {
        return $this->problemRepo->getTotalPublicCount();
    }

    public function getTotalCount():int
    {
        return $this->problemRepo->getTotalCount();
    }

    public function getProblems(int $userId = -1,int $page,int $size)
    {
        $problems = $this->problemRepo->getProblems($page,$size)->toArray();

        //重新组织数组形式
        $data = [];
        $problemIds = [];

        $singleProblem = $problems[0];
        $problemIds[] = $problems[0]['id'];

        $tags = [];
        if($problems[0]['tag_id'] != null)
            $tags[] = ['tag_title' => $problems[0]['tag_title'] , 'tag_id' => $problems[0]['tag_id']];

        if(count($problems) > 1)
        {
            for($i=1;$i<count($problems);$i++)
            {
                if($singleProblem['id'] == $problems[$i]['id'])
                {
                    if($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['tag_title'] , 'tag_id' => $problems[$i]['tag_id']];
                }
                else
                {
                    $singleProblem['tags'] = $tags;
                    unset($singleProblem['tag_id']);
                    unset($singleProblem['tag_title']);
                    $data[] = $singleProblem;
                    $singleProblem = $problems[$i];
                    $tags = [];
                    if($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['tag_title'] , 'tag_id' => $problems[$i]['tag_id']];
                }

                $problemIds[] = $problems[$i]['id'];
            }
        }

        //剩下最后一个题目
        $singleProblem['tags'] = $tags;
        unset($singleProblem['tag_id']);
        unset($singleProblem['tag_title']);
        $data[] = $singleProblem;

        //组织用户解题情况

        if($userId != -1) {
            $problemIds = [];

            foreach ($data as $problem) {
                $problemIds[] = $problem['id'];
            }

            $userStatuses = $this->solutionRepo->getSolutionsIn('user_id', $userId, 'problem_id', $problemIds, ['problem_id', 'result'])->toArray();

            $subIds = $acIds = [];

            foreach ($userStatuses as $userStatus) {
                $subIds[$userStatus['problem_id']] = true;
                if($userStatus['result'] == 4) $acIds[$userStatus['problem_id']] = true;
            }

            foreach ($data as &$problem) {
                if (isset($subIds[$problem['id']]))
                {
                    if(isset($acIds[$problem['id']]))
                        $problem['user_status'] = 'Y';
                    else
                        $problem['user_status'] = 'N';
                }
                else $problem['user_status'] = null;
            }
        }

        return $data;
    }

    //组织数据 转化md为markdown

    public function getProblemById(int $problemId,array $columns = ['*'])
    {
        //join过的表不能再简单的用原表主键找   
        $problems = $this->problemRepo->getBy('problems.id',$problemId,$columns)->toArray();
        //拿到的全部的数据

        if(count($problems) == 0)
            return false;

        //重新组装
        $data = $problems[0];
        $data['tags'] = [];

        if(count($problems)>1)
        {
            foreach ($problems as $problem)
                $data['tags'][] = [
                    'tag_id' => $problem['tag_id'],
                    'tag_title' => $problem['tag_title']
                ];

        }
        else
        {
            if($data['tag_id']!=null)
                $data['tags'][] = [
                    'tag_id' => $data['tag_id'],
                    'tag_title' => $data['tag_title']
                ];
        }
        unset($data['tag_id']);
        unset($data['tag_title']);

        //转换markdown内容
        $data['description'] = $this->converter->convertToHtml($data['description']);
        $data['input'] = $this->converter->convertToHtml($data['input']);
        $data['output'] = $this->converter->convertToHtml($data['output']);

        return $data;
    }

    public function getProblemBy(string $param, $value,array $columns = ['*'])
    {
        $problems = $this->problemRepo->getBy($param,$value,$columns)->toArray();
        //拿到的全部的数据

        if(empty($problems))
            return false;

        //重新组装
        $data = $problems[0];
        $data['tags'] = [];

        if(count($problems)>1)
        {
            foreach ($problems as $problem)
                $data['tags'][] = [
                    'tag_id' => $problem['tag_id'],
                    'tag_title' => $problem['tag_title']
                ];
        }
        else
        {
            if($data['tag_id']!=null)
                $data['tags'] = [
                    'tag_id' => $data['tag_id'],
                    'tag_title' => $data['tag_title']
                ];
        }

        unset($data['tag_id']);
        unset($data['tag_title']);

        return $data;
    }

    public function getProblemByMult(array $condition,array $columns = ['*'])
    {
        //缺少组装
        return $this->problemRepo->getByMult($condition,$columns)->first()->toArray();
    }

    public function isProblemExist(int $problemId): bool
    {
        return $this->problemRepo->get($problemId,['id'])->first()!=null;
    }

    /**
     * 修改题目信息
     */

    public function updateProblem(int $problemId, array $problemData,array $testData):bool
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
     * 提交题目
     */

    public function submitProblem(int $problemId,array $data,int $problemNum = -1):int
    {
        //写入solution和source_code
        //插入顺序必须是先插入source_code获取id然后再给solution不然一定会编译错误。
        //提交成功后返回solution_id否则返回0
        //题目组中的题目插入时附带题目编号，默认-1

        $code = [
            'source' => $data['source_code'],
            'private' => $data['private']
        ];

        $solutionId = 0;

        $solutionData = [
            'problem_id' => $problemId,
            'problem_num' => $problemNum,
            'problem_group_id' => $data['problem_group_id'],
            'user_id' => $data['user_id'],
            'ip' => $data['ip'],
            'language' => $data['language'],
            'result' => 0,
            'code_length' => $data['code_length']
        ];

        //开启事务处理

        DB::transaction(function ()use(&$solutionId,$code,$solutionData){
            //请注意！如果要在闭包里改变外部变量的值必须传引用
            $solutionId = $this->sourceRepo->insertWithId($code);
            $solutionData['id'] = $solutionId;
            $this->solutionRepo->insert($solutionData);
        });


        return $solutionId;
    }

    /**
     * 删除题目（软删除并加入日志）
     */

    public function deleteProblem(User $user,int $problemId): bool
    {
        $flag = true;

        //TODO: 删除一道题目会涉及到很多其他的表，随着以后系统的扩充慢慢完善这个方法的内容
        DB::transaction(function () use($user,$problemId,&$flag){
            //删除题目表
            if($this->problemRepo->deleteWhere(['id'=>$problemId])!=1) $flag = false;
            //创建删除日志的条目
            $data = [
                'table_name' => 'Problem',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'key' => $problemId
            ];

            if(!$this->deletionService->createDeletion($data)) $flag = false;

            //包装tag关系信息
            $tagRelations = $this->tagRelationRepo->getBy('problem_id',$problemId,['id']);
            //创建删除记录
            if($tagRelations->count()>0)
            {
                $data = [];

                foreach ($tagRelations as $tagRelation)
                {
                    array_push($data,[
                        'table_name' => 'ProblemTagRelation',
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'key' => $tagRelation->id
                    ]);
                }

                $this->deletionService->createDeletions($data);
                //删除tag关系
                $this->tagRelationRepo->deleteWhere(['problem_id' => $problemId]);
            }

        });

        return $flag;
    }

    /**
     * 搜索
     */

    public function searchProblemsCount(string $likeName): int
    {
       $pattern = "%".$likeName."%";
       return $this->problemRepo->getWhereLikeCount($pattern);
    }

    public function searchProblems(int $userId = -1,string $likeName, int $start, int $size)
    {
        $pattern = "%".$likeName."%";

        $problems = $this->problemRepo->getWhereLike($pattern,$start,$size)->toArray();

        //重新组织数组形式
        $data = [];

        $singleProblem = $problems[0];
        $tags = [];
        if($problems[0]['tag_id'] != null)
            $tags[] = ['tag_title' => $problems[0]['tag_title'] , 'tag_id' => $problems[0]['tag_id']];

        if(count($problems) > 1)
        {
            for($i=1;$i<count($problems);$i++)
            {
                if($singleProblem['id'] == $problems[$i]['id'])
                {
                    if($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['tag_title'] , 'tag_id' => $problems[$i]['tag_id']];
                }
                else
                {
                    $singleProblem['tags'] = $tags;
                    unset($singleProblem['tag_id']);
                    unset($singleProblem['tag_title']);
                    $data[] = $singleProblem;
                    $singleProblem = $problems[$i];
                    $tags = [];
                    if($problems[$i]['tag_id'] != null)
                        $tags[] = ['tag_title' => $problems[$i]['tag_title'] , 'tag_id' => $problems[$i]['tag_id']];
                }
            }
        }

        //剩下最后一个题目
        $singleProblem['tags'] = $tags;
        unset($singleProblem['tag_id']);
        unset($singleProblem['tag_title']);
        $data[] = $singleProblem;

        //组织用户解题情况

        if($userId != -1) {
            $problemIds = [];

            foreach ($data as $problem) {
                $problemIds[] = $problem['id'];
            }

            $userStatuses = $this->solutionRepo->getSolutionsIn('user_id', $userId, 'problem_id', $problemIds, ['problem_id', 'result'])->toArray();

            $subIds = $acIds = [];

            foreach ($userStatuses as $userStatus) {
                $subIds[$userStatus['problem_id']] = true;
                if($userStatus['result'] == 4) $acIds[$userStatus['problem_id']] = true;
            }

            foreach ($data as &$problem) {
                if (isset($subIds[$problem['id']]))
                {
                    if(isset($acIds[$problem['id']]))
                        $problem['user_status'] = 'Y';
                    else
                        $problem['user_status'] = 'N';
                }
                else $problem['user_status'] = null;
            }
        }

        return $data;
    }

    /**
     * 以文件形式获取题解数据
     */

    public function getRunDataPath(int $problemId,string $name)
    {
        $path = $this->getPath($problemId);

        if(File::isDirectory($path))
        {
            switch ($name)
            {
                case "test_in":
                    return $path."test.in";
                case "test_out":
                    return $path."test.out";
                case "sample_in":
                    return $path."sample.in";
                case "sample_out":
                    return $path."sample.out";
                default:
                    return null;
            }

        }

        return null;
    }
}