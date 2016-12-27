<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:15
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class ProblemGroupRelationRepository extends AbstractRepository implements SoftDeletionInterface
{
    public function model()
    {
        return "NEUQOJ\Models\ProblemGroupRelation";
    }

    use SoftDeletionTrait;
}