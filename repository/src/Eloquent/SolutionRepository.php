<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午3:52
 */

namespace NEUQOJ\Repository\Eloquent;


class SolutionRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Solution";
    }
}