<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:34
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class ProblemRepository extends AbstractRepository implements SoftDeletionInterface
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Problem";
    }

    use SoftDeletionTrait;
}