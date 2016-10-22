<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-20
 * Time: 下午10:39
 */

namespace NEUQOJ\Repository\Eloquent;

use Illuminate\Contracts\Auth\Authenticatable;

class UserRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\User";
    }
}