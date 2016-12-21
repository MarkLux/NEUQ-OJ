<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午8:52
 */

namespace NEUQOJ\Repository\Eloquent;


class ProblemKeyRepository extends AbstractRepository
{
    public function Model()
    {
        return "NEUQOJ\Repository\Models\ProblemKey";
    }

}