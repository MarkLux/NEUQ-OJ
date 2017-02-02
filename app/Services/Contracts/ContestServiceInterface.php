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

    function getContest(int $contestId,array $columns = ['*']);

    function getContestIndex(int $userId,int $groupId);

    function getProblem(int $groupId,int $problemNum);

    function getInContestByPassword(int $userId,int $groupId,string $password):bool;

    function createContest(array $data,array $problems,array $users=[]):int;

    function updateContestProblem(int $groupId,array $problemIds):bool;

    function deleteContest(int $groupId):bool;

    function getContestDetail(int $groupId);

    function updateContestInfo(int $groupId,array $datas):bool;

    function resetContestPassword(int $groupId,string $password):bool;

    function resetContestPermission(int $groupId,array $users):bool;

    function getRankList(int $groupId);

    function searchContest(string $keyword,int $page,int $size);

    function getStatus(int $groupId,int $page,int $size,array $conditions=[]);

    function isContestExist(int $groupId):bool;

    function submitProblem(int $userId,int $groupId,int $problemNum,array $data):int;

    function canUserAccessContest(int $userId,int $groupId):bool;

    function isUserContestCreator(int $userId,int $groupId):bool;
}