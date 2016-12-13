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

    function getProblemById(int $problemId);

    function getProblemBy(string $param,$value);

    function getProblemByMult(array $condition);

    //以文件形式输出测试输入输出等数据
    function getRunDatas(int $problemId);

    //查找
    //宽泛检索
    //提前制定好需要查找的字段

    function searchProblemsCount(string $likeName):int;

    function searchProblems(string $likeName,int $start,int $size);

    //创建

    function addProblem(array $problemData,array $testData):bool;

   //修改

    function updateProblem(int $problemId,array $problemData,array $testData):bool;

    //删除

    function deleteProblem(User $user,int $problemId):bool;

    //判题

    function submitProlem(int $problemId,array $data);

    function getProblemStatus(int $userId,int $problemId);

    /*
    *状态辅助函数
    */

    function isProblemExist(int $problemId):bool;

    function isProblemHasKey(int $problemId):bool;

    function isUserAcProblem(int $userId,int $problemId):bool;

    /*
    *讨论版
    */

    function getProblemDiscussion(int $problemId);

    /*
    *tag
    */

    function addTagToProblem(int $problemId,int $tagId);

    function deleteTagFromProblem(int $problemId,int $tagId);

    //注意检查unique
    function addTag(string $tagName);

    function deleteTag(int $tagId);

    function getTagId(string $tagname);

    function getTagName(int $tagId);

    /**
     * 题解problem_key
     */

    function addProblemKey(int $problemId,array $data);

    function updateProblemKey(int $problemId,array $data);

    function deleteProblemKey(int $problemId);

}