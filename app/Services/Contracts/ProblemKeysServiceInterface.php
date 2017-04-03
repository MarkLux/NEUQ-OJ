<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午8:36
 */

namespace NEUQOJ\Services\Contracts;


use NEUQOJ\Repository\Models\Problem;

interface ProblemKeysServiceInterface
{
    function addProblemKey(array $data):bool;

    function deleteProblemKey(int $problemId):bool;

    function updateProblemKey(array $condition,array $data):bool;

    function getProblemKey(int $problemId);

    function canUserAccessKey(int $problemId,int $userId);
}