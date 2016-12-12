<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:19
 */

namespace NEUQOJ\Services\Contracts;


interface ProblemServiceInterface
{
    /*
      *单条查询
      */

    function getProblemById(int $problemId);

    function getProblemBy(string $param,$value);

    function getProblemByMult(array $condition);

    /*
    *查找
    */

    //宽泛检索
    //提前制定好需要查找的字段

    function searchProblemsCount(string $likeName):int;

    function searchProblems(string $likeName,int $start,int $size);

    //题解,每道题目暂时只给设立一个题解位置

    function getProblemKey(int $problemId);

    /*
    *创建 要牵扯到文件系统
    */

    function addProblem(array $data):bool;

    function addProblemKey(int $problemId,array $data);

    /*
    *修改
    */

    function updateProblem(int $problemId,array $data);

    function updateProblemKey(int $problemId,array $data);

    /*
    *删除
    */

    function deleteProblem(int $problemId);

    function deleteProblemKey(int $problemId);

    /*
    *状态辅助函数
    */

    function isProblemHasKey(int $problemId):bool;

    function isUserAcProblem(int $userId,int $problemId):bool;

    /*
    *判题核心联动部分
    */

    function submitProlem(int $problemId,array $data);

    function getProblemStatus(int $problemId);

    /*
    *讨论版联动部分
    */

    function getProblemDiscussion(int $problemId);

    /*
    *tag功能
    */

    function addTagToProblem(int $problemId,int $tagId);

    function deleteTagFromProblem(int $problemId,int $tagId);

    //注意检查unique
    function addTag(string $tagName);

    function deleteTag(int $tagId);

    function getTagId(string $tagname);

    function getTagName(int $tagId);
}