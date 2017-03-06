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

    function getProblemByNum(int $groupId,int $problemNum);

    /*
     * 状态辅助函数
     */

    function isProblemGroupExist(int $groupId):bool;
    function isUserGroupCreator(int $userId,int $groupId):bool;

    /*
     * 创建
     */

    function createProblemGroup(array $data,array $problems):int;

    /*
     * 删除
     */

    function deleteProblemGroup(int $groupId):bool;

    /*
     * 修改
     */

    function updateProblemGroup(int $groupId,array $data):bool;

//    function submitProblem(int $groupId,int $problemId,array $data):int;

    /*
     * 题目部分
     */

    function updateProblems(int $groupId,array $problems):bool;

    /*
     * 核心部分
     */

    function getSolutionCount(int $groupId):int;

    function getSolutions(int $groupId,int $page,int $size,array $conditions=[]);

    /*
     * 权限部分
     */

    function getGroupAdmissions(int $groupId);

    function resetGroupAdmissions(int $groupId,array $newData):bool;

    function checkLang(int $langCode,int $langmask):bool;

}