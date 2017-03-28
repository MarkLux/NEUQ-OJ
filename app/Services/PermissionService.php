<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/27
 * Time: 下午8:00
 */

namespace NEUQOJ\Services;


use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\RoleExistedException;
use NEUQOJ\Exceptions\RoleNotExistException;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\RolePriRepository;
use NEUQOJ\Repository\Eloquent\RoleRepository;
use NEUQOJ\Repository\Eloquent\UserRoleRepository;
use NEUQOJ\Services\Contracts\PermissionServiceInterface;

class PermissionService implements PermissionServiceInterface
{
    private $roleRepository;

    private $privilegeRepository;

    private $rolePriRelationRepository;

    private $userRoleRelationRepository;

    // 这个因为要注册到服务容器，所以不type-hint，直接在内部解析

    public function __construct()
    {
        $app = Container::getInstance();
        $this->roleRepository = $app->make('NEUQOJ\Repository\Eloquent\RoleRepository');
        $this->privilegeRepository = $app->make('NEUQOJ\Repository\Eloquent\PrivilegeRepository');
        $this->rolePriRelationRepository = $app->make('NEUQOJ\Repository\Eloquent\RolePriRepository');
        $this->userRoleRelationRepository = $app->make('NEUQOJ\Repository\Eloquent\UserRoleRepository');
    }


    /**
     * 角色
     */
    public function createRole(array $role, array $privileges): bool
    {
        // 检查角色名是否已经被占用

        $role = $this->roleRepository->getBy('name', $role['name'], ['name'])->first();

        if ($role != null) {
            throw new RoleExistedException();
        }

        // 因为权限名是直接写在代码里的，如果有不存在的权限名被写入，
        // 不会对系统造成影响，但是如果有历史修改，一定要确保之前的
        // 权限名不会出现在代码中

        $relations = [];

        foreach ($privileges as $privilege) {
            $relations[] = [
                'role_name' => $role['name'],
                'privilege_name' => $privilege
            ];
        }

        $flag = false;

        DB::transaction(function () use ($role, $relations, &$flag) {
            $this->roleRepository->insert($role);
            $this->rolePriRelationRepository->insert($relations);
            $flag = true;
        });

        return $flag;
    }

    // 这个更新方法，只能让你修改角色的其他相关信息。
    // 角色的name作为标识符，一旦创建就不能更改
    // 除非删除原角色，再新建角色

    public function updateRole(string $roleName, array $role, array $privileges): bool
    {
        $oldRole = $this->roleRepository->getBy('name', $roleName, ['name'])->first();

        if ($oldRole == null) {
            throw new RoleNotExistException();
        }

        // 清除可能混杂的name字段，防止出现错误
        if (isset($role['name'])) {
            unset($role['name']);
        }

        $relations = [];

        foreach ($privileges as $privilege) {
            $relations[] = [
                'role_name' => $role['name'],
                'privilege_name' => $privilege
            ];
        }

        $flag = false;

        DB::transaction(function () use ($roleName, $role, $relations, &$flag) {
            $this->roleRepository->updateWhere(['name' => $roleName], $role);
            // 更新权限列表，全部删除然后再重新插入（虽然有点麻烦）
            $this->rolePriRelationRepository->deleteWhere(['role_name' => $roleName]);
            $this->rolePriRelationRepository->insert($relations);
            $flag = true;
        });

        return $flag;

    }

    public function deleteRole(string $roleName): bool
    {
        $oldRole = $this->roleRepository->getBy('name', $roleName, ['name'])->first();

        if ($oldRole == null) {
            throw new RoleNotExistException();
        }

        $flag = false;

        DB::transaction(function () use ($roleName, &$flag) {
            // 删除所有权限关系
            $this->rolePriRelationRepository->deleteWhere(['role_name' => $roleName]);
            // 删除所有用户关系
            $this->userRoleRelationRepository->deleteWhere(['role_name' => $roleName]);
            // 删除角色
            $this->roleRepository->deleteWhere(['name' => $roleName]);

            $flag = true;
        });

        return $flag;
    }

    public function getAllRole()
    {
        return $this->roleRepository->all([
            'name',
            'display_name'
        ]);
    }

    public function getRole(string $roleName)
    {
        return $this->roleRepository
            ->getBy('name', $roleName)
            ->first();
    }

    public function getUseRole(int $userId)
    {
        return $this->userRoleRelationRepository
            ->getBy('user_id', $userId);
    }

    /**
     * 权限
     */
    public function getAllPrivileges()
    {
        return $this->privilegeRepository->all([
            'name',
            'display_name'
        ]);
    }

    public function getPrivilege(string $privilegeName)
    {
        return $this->privilegeRepository
            ->getBy('name', $privilegeName)
            ->first();
    }

    /**
     * 授权
     */

    public function updateUserRole(int $userId, array $roles): bool
    {
        $flag = false;

        $relations = [];

        foreach ($roles as $role) {
            $relations[] = [
                'user_id' => $userId,
                'role_name' => $role
            ];
        }

        DB::transaction(function () use ($userId, &$flag, $relations) {
            // 删除原有所有关系
            $this->userRoleRelationRepository->deleteWhere(['user_id' => $userId]);
            $this->userRoleRelationRepository->insert($relations);

            $flag = true;
        });

        return $flag;
    }

    /**
     *  检查
     */
    public function checkPermission(int $userId, array $privileges): bool
    {
        $userPrivileges = $this->userRoleRelationRepository->getUserPrivileges($userId);

        // 剥壳

        $checks = [];

        foreach ($userPrivileges as $userPrivilege) {
            // 利用索引来判断
            $checks[$userPrivilege->privilege_name] = true;
        }

        foreach ($privileges as $privilege) {
            if(!isset($checks[$privilege])){
                return false;
            }
        }

        return true;

    }

    public function checkRole(int $userId, string $roleName): bool
    {
        $relation = $this->userRoleRelationRepository->getByMult([
            'user_id' => $userId,
            'role_name' => $roleName
        ])->first();

        return $relation != null;
    }
}