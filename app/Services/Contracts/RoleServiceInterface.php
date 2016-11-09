<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:19
 */

namespace NEUQOJ\Services\Contracts;


interface RoleServiceInterface
{
    function hasRole(int $userId,string $role):bool;

    function createRole(array $data);

    function giveRoleTo(int $userId,string $role);

    function getRole(string $name):Role;

    function deleteRole($roleId);

    function updateRole(array $condition,array $data);
}