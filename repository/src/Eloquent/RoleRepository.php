<?php
/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-11
 * Time: 下午9:51
 */
namespace NEUQOJ\Repository\Eloquent;
class RoleRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\Role";
    }
}