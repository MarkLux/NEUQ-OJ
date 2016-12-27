<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:34
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\InsertWithIdTrait;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ProblemRepository extends AbstractRepository implements SoftDeletionInterface
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Problem";
    }

    use SoftDeletionTrait;

    use InsertWithIdTrait;

    function getTotalCount()
    {
        return $this->all()->count();
    }

    function getProblems(int $page,int $size)
    {
        //其实这个接口不应该取出problems里的description,影响速度

        return $this->model
            ->select('problems.id','problems.title','problems.difficulty','problems.source','problems.submit','problems.solved',
                'problems.is_public','problems.created_at','problems.updated_at','problem_tag_relations.tag_id',
                'problem_tag_relations.tag_title')
            ->leftJoin('problem_tag_relations','problems.id','=','problem_tag_relations.problem_id')
            ->orderBy('problems.id')
            ->skip($size * --$page)
            ->take($size)
            ->get();
    }

    function getBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->model
            ->where($param, $value)
            ->select('problems.*','problem_tag_relations.tag_id','problem_tag_relations.tag_title')
            ->leftJoin('problem_tag_relations','problems.id','=','problem_tag_relations.problem_id')
            ->orderBy('problems.id')
            ->get();
    }

    //覆盖方法

    function doDeletion(int $id): bool
    {
        $item =  $this->model->where('id',$id)->onlyTrashed()->get()->first();

        if($item == null)
            return false;
        if(!$item->forceDelete())
            return false;

        //删除文件系统中的相关内容
        //文件操作写在这里并不合适，但是由于系统文件结构并不复杂所以就这么写了

        $path = '/home/judge/data/'.$id.'/';

        if(File::isDirectory($path))
            return File::deleteDirectory($path);

    }

    /*
     * 搜索
     */

    function getWhereLikeCount(string $pattern):int
    {
        //join过后的表的总数会出现不必要的重复，需要检测

        $problems = $this->model
            ->leftJoin('problem_tag_relations','problems.id','=','problem_tag_relations.problem_id')
            ->select('problems.id')
            ->where('problems.title','like',$pattern)
            ->orWhere('problems.source','like',$pattern)
            ->orWhere('problems.creator_name','like',$pattern)
            ->orWhere('problem_tag_relations.tag_title','like',$pattern)
            ->get();

        if($problems->first() == null)
            return 0;

        $count = $problems->count();


        $tempId = $problems->first()->id;

        foreach ($problems as $problem)
        {
            if($problem->id == $tempId)
                $count--;
            $tempId = $problem->id;
        }

        return $count+1;
    }

    //简易like搜索
    function getWhereLike(string $pattern,int $page = 1,int $size = 15,array $columns = ['*'])
    {
        if(!empty($size))
        {
            return $this->model
                ->leftJoin('problem_tag_relations','problems.id','=','problem_tag_relations.problem_id')
                ->where('problems.title','like',$pattern)
                ->orWhere('problems.source','like',$pattern)
                ->orWhere('problems.creator_name','like',$pattern)
                ->orWhere('problem_tag_relations.tag_title','like',$pattern)
                ->select('problems.id','problems.title','problems.difficulty','problems.source','problems.submit','problems.solved',
                    'problems.is_public','problems.created_at','problems.updated_at','problem_tag_relations.tag_id',
                    'problem_tag_relations.tag_title')
                ->orderBy('problems.id')
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        }

        return null;
    }
}
