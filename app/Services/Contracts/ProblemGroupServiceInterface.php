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

    function removeProblem(int $groupId,int $problemNum):bool;

    function addProblem(int $groupId,int $problemId,int $score=null):bool;

    /*
     * 核心部分
     */

    function getSolutionCount(int $groupId):int;

    function getSolutions(int $page,int $size);

}