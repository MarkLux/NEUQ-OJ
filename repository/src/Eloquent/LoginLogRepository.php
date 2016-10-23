<?php

namespace NEUQOJ\Repository\Eloquent;

class LoginLogRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQ\Repository\Models\LoginLog";
    }
}