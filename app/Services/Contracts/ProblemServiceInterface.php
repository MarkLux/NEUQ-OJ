<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:19
 */

namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;


interface ProblemServiceInterface
{
    /*
      *题目基础内容
      */

    //获取

    function getTotalCount():int;

    function getTotalPublicCount():int;

    function getProblems(int $userId = -1,int $page,int $size);

    function getProblemById(int $problemId,array $columns = ['*']);

    function getProblemBy(string $param,$value,array $columns = ['*']);

    function getProblemByMult(array $condition,array $columns = ['*']);

    //以文件形式输出测试输入输出等数据
    function getRunDataPath(int $problemId,string $name);

    //查找
    //宽泛检索
    //提前制定好需要查找的字段

    function searchProblemsCount(string $likeName):int;

    function searchProblems(string $likeName,int $start,int $size);

    //创建

    function addProblem(User $user,array $problemData,array $testData):int;

   //修改

    function updateProblem(int $problemId,array $problemData,array $testData):bool;

    //删除

    function deleteProblem(User $user,int $problemId):bool;

    //判题

    function submitProblem(int $problemId,array $data):int;

    /*
    *状态辅助函数
    */

    function isProblemExist(int $problemId):bool;

    function canUserAccessProblem(int $userId,int $problemId):bool;
}