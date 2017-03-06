<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:58
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class UserGroupRelationRepository extends AbstractRepository implements SoftDeletionInterface
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserGroupRelation";
    }

    function getMemberCountById(int $groupId):int
    {
        return $this->model->where('group_id',$groupId)->count();
    }

    use SoftDeletionTrait;
}