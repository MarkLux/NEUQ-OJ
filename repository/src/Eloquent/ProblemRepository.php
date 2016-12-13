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
}