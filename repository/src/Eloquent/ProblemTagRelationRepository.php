<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午1:07
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class ProblemTagRelationRepository extends AbstractRepository implements SoftDeletionInterface
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\ProblemTagRelation";
    }

    use SoftDeletionTrait;
}