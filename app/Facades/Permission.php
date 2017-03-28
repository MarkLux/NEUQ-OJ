<?php

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/28
 * Time: 下午8:52
 */

namespace NEUQOJ\Facades;

use Illuminate\Support\Facades\Facade;

class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PermissionService';
    }
}