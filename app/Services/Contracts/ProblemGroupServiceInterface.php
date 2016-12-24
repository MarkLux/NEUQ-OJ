<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-22
 * Time: 下午1:48
 */

namespace NEUQOJ\Services\Contracts;

interface ProblemGroupServiceInterface
{
    /*
     *获取
     */

    function getProblemGroup(int $groupId,array $columns = ['*']);

    function getProblemGroupBy(string $param,string $value,array $columns = ['*']);

    /*
     * 状态辅助函数
     */

    function isProblemGroupExist(int $groupId):bool;

    /*
     * 创建
     */

    function createProblemGroup(array $data):int;

    /*
     * 删除
     */

    function deleteProblemGroup(int $groupId):bool;

    /*
     * 修改
     */

    function updateProblemGroup(int $groupId,array $data):bool;
}