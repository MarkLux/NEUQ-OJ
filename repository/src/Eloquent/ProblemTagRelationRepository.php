<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-15
 * Time: 下午7:43
 */

namespace NEUQOJ\Repository\Eloquent;


class ProblemTagRelationRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\ProblemTagRelation";
    }
}