<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:15
 */

namespace NEUQOJ\Repository\Eloquent;


class ProblemGroupRelationRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Models\ProblemGroupRelation";
    }
}