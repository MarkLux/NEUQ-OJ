<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:38
 */

namespace NEUQOJ\Repository\Eloquent;


class DiscussionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Discussion";
    }
}