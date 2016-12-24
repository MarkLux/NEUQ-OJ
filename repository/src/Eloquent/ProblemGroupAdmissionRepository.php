<?php

namespace NEUQOJ\Repository\Eloquent;

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:17
 */
class ProblemGroupAdmissionRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\ProblemGroupAdmission";
    }
}