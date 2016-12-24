<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午1:53
 */

namespace NEUQOJ\Repository\Contracts;

use NEUQOJ\Repository\Models\User;

interface ContestServiceInterface
{
    function getAllContests(int $page,int $size);

    function createContest(array $data,array $users=[]):int;

    function deleteContest(int $groupId):bool;

    function updateContest(int $groupId,array $data):bool;

    function resetContestPassword(int $groupId,string $password):bool;

    function resetContestPermission(int $groupId,array $users):bool;

    function getRankList(int $groupId);

    function searchContest(string $keyword,int $page,int $size);

    function getStatus(int $groupId);

    function isContestExist(int $groupId):bool;

    function submitProblem(User $user,int $groupId,int $problemId);

    function canUserAccessContest(int $userId,int $groupId):bool;
}