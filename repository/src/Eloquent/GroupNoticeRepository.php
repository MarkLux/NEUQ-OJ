<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-25
 * Time: 下午8:58
 */

namespace NEUQOJ\Repository\Eloquent;


class GroupNoticeRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\GroupNotice";
    }
}