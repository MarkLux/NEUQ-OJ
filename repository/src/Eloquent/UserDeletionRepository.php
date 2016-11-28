<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:49
 */

namespace NEUQOJ\Repository\Eloquent;


class UserDeletionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserDeletion";
    }
}