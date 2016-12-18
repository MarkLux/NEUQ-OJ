<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/12/18
 * Time: 下午2:53
 */

namespace NEUQOJ\Repository\Eloquent;



class DiscussionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Discussion";
    }
}