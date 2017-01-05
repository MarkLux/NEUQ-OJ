<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午1:53
 */

namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;

interface ContestServiceInterface
{
    function getAllContests(int $page,int $size);

    function getContest(int $userId,int $groupId);

    function getProblem(int $groupId,int $problemNum);

    function getInContestByPassword(int $userId,int $groupId,string $password):bool;

    function createContest(array $data,array $problems,array $users=[]):int;

    function deleteContest(int $groupId):bool;

    function updateContest(int $groupId,array $data):bool;

    function resetContestPassword(int $groupId,string $password):bool;

    function resetContestPermission(int $groupId,array $users):bool;

    function getRankList(int $groupId);

    function searchContest(string $keyword,int $page,int $size);

    function getStatus(int $groupId);

    function isContestExist(int $groupId):bool;

    function submitProblem(int $groupId,int $problemNum,array $data):int;

    function canUserAccessContest(int $userId,int $groupId):bool;
}