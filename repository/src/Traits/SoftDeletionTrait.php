<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午5:32
 */

namespace NEUQOJ\Repository\Traits;

trait SoftDeletionTrait
{
    function doDeletion(int $id): bool
    {
        $item =  $this->model->where('id',$id)->onlyTrashed()->get()->first();

        if($item == null)
            return false;
        return $item->forceDelete();
    }

    function undoDeletion(int $id): bool
    {
        $item =  $this->model->where('id',$id)->onlyTrashed()->get()->first();
        if($item == null)
            return false;
        return $item->restore();
    }
}