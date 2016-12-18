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

class ProblemRepository extends AbstractRepository implements SoftDeletionInterface
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Problem";
    }

    use SoftDeletionTrait;

    use InsertWithIdTrait;

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
        //在三个字段中搜索
        //TODO: join之后题目总数变动 需要修正 考虑删除这个函数直接在search里组装total_page等信息

        return $this->model
            ->leftJoin('problem_tag_relations','problems.id','=','problem_tag_relations.problem_id')
            ->select('problems.title','problems.source','problems.creator_name','problem_tag_relations.tag_title')
            ->where('problems.title','like',$pattern)
            ->orWhere('problems.source','like',$pattern)
            ->orWhere('problems.creator_name','like',$pattern)
            ->orWhere('problem_tag_relations.tag_title','like',$pattern)
            ->count();
    }

    //简易like搜索
    function getWhereLike(string $pattern,int $page = 1,int $size = 15,array $columns = ['*'])
    {
        if(!empty($size))
        {
            return $this->model
                ->where('title','like',$pattern)
                ->orWhere('source','like',$pattern)
                ->orWhere('creator_name','like',$pattern)
                ->skip($size * --$page)
                ->take($size)
                ->get($columns);
        }
    }
}
