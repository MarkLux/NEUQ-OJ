<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/4/13
 * Time: 上午12:38
 */

namespace NEUQOJ\Repository\Traits;


trait GetWhereCountTrait
{
    function getWhereCount(array $conditions = []):int
    {
        if ($conditions == [])
            return $this->model->count();

        return $this->model
            ->where($conditions)
            ->count();
    }
}