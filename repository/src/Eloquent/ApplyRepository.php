<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-28
 * Time: 下午9:41
 */

namespace NEUQOJ\Repository\Eloquent;

class ApplyRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Apply";
    }
}