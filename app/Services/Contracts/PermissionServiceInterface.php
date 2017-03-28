<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/27
 * Time: 下午7:46
 */

namespace NEUQOJ\Services\Contracts;


interface PermissionServiceInterface
{
    /**
     * 角色
     */

    function createRole(array $role,array $privileges):bool;

    function updateRole(string $roleName,array $role,array $privileges):bool;

    function deleteRole(string $roleName):bool;

    // 用于获取用户列表

    function getAllRole();

    // 获取一个角色的详细授权情况

    function getRole(string $userName);

    // 获取一个用户所有的角色

    function getUseRole(int $userId);

    /**
     * 权限
     */

    function getAllPrivileges();

    function getPrivilege(string $privilegeName);

    /**
     * 授权
     */

    function updateUserRole(int $userId,array $roles):bool;

    /**
     *  检查
     */

    function checkPermission(int $userId,array $privileges):bool;

    function checkRole(int $userId,string $roleName):bool;

}