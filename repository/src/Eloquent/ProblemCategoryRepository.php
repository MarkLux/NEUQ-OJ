<?php
/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-23
 * Time: 下午8:52
 */
namespace NEUQOJ\Repository\Eloquent;

class ProblemCategoryRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\ProblemCategory";
    }
}