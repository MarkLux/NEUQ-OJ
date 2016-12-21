<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:41
 */

namespace NEUQOJ\Services;


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

    public function getProblems(int $page,int $size)
    {
        $problems = $this->problemRepo->getProblems($page,$size);
        //重新组装数据
        $data = [];
        $single = [];

        $temp_id = 0;

        foreach ($problems as $problem)
        {
            if($problem->id == $temp_id)
            {
                //是上一个数据,继续组装single
                array_push($single['tags'],[
                    'tag_id' => $problem->tag_id,
                    'tag_title'=>$problem->tag_title
                ]);
            }
            else
            {
                if(!empty($single))
                    array_push($data,$single);
                $temp_id = $problem->id;
                $single = $problem->toArray();
                $single['tags'] = [];
                if($problem->tag_id!=null)
                    array_push($single['tags'],[
                        'tag_id' => $problem->tag_id,
                        'tag_title' => $problem->tag_title
                    ]);
                unset($single['tag_id']);
                unset($single['tag_title']);
            }
        }

        //还有一个数据没有组装进去，再组装
        array_push($data,$single);

        return $data;
    }

    public function getProblemById(int $problemId,array $columns = ['*'])
    {
        //join过的表不能再简单的用原表主键找   
        $problems = $this->problemRepo->getBy('problems.id',$problemId,$columns)->toArray();
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
        return $this->problemRepo->getByMult($condition,$columns)->first();
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

    public function submitProlem(User $user,int $problemId,array $data):int
    {
        //写入solution和source_code
        //插入顺序必须是先插入source_code获取id然后再给solution不然一定会编译错误。
        //提交成功后返回solution_id否则返回0

        $code = [
            'source' => $data['source_code'],
            'private' => $data['private']
        ];

        $solutionId = 0;

        $solutionData = [
            'problem_id' => $problemId,
            'problem_group_id' => $data['problem_group_id'],
            'user_id' => $user->id,
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

    public function searchProblems(string $likeName, int $start, int $size)
    {
        $pattern = "%".$likeName."%";

        $problems = $this->problemRepo->getWhereLike($pattern,$start,$size);

        $data = [];
        $single = [];

        $temp_id = 0;

        foreach ($problems as $problem)
        {
            if($problem->id == $temp_id)
            {
                //是上一个数据,继续组装single
                array_push($single['tags'],[
                    'tag_id' => $problem->tag_id,
                    'tag_title'=>$problem->tag_title
                ]);
            }
            else
            {
                if(!empty($single))
                    array_push($data,$single);
                $temp_id = $problem->id;
                $single = $problem->toArray();
                $single['tags'] = [];
                array_push($single['tags'],[
                    'tag_id' => $problem->tag_id,
                    'tag_title' => $problem->tag_title
                ]);
                unset($single['tag_id']);
                unset($single['tag_title']);
            }
        }

        //还有一个数据没有组装进去，再组装
        array_push($data,$single);

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