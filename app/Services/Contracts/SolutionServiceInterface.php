<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-17
 * Time: 下午5:15
 */

namespace NEUQOJ\Services\Contracts;

interface SolutionServiceInterface
{
    function getAllSolutions(int $page,int $size,array $condition);

    function getSolutionBy(string $param,$value,array $columns = ['*']);

    function getSolution(int $solutionId,array $columns = ['*']);

    function isSolutionExist(int $solutionId):bool;

    function getSourceCode(int $solutionId);

    function getRuntimeInfo(int $solutionId);

    function getCompileInfo(int $solutionId);

    function getSolutionCount():int;

    //TODO： 查重功能
}