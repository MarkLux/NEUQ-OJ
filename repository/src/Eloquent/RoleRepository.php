<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-19
 * Time: 上午5:07
 */

namespace NEUQOJ\Repository\Eloquent;


class RoleRepository extends AbstractRepository
{
    function Model()
    {
        return "NEUQOJ\Repository\Models\Role";
    }

}