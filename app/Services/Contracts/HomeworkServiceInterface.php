<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/20
 * Time: 下午2:49
 */
namespace NEUQOJ\Services\Contracts;

interface HomeworkServiceInterface
{
    // gets

    function getHomeworks(int $groupId,int $page,int $size,int $type);

    function getHomework(int $homeworkId,array $columns=['*']);

    function getHomeworkBy(string $param,string $value,array $columns=['*']);

    function getHomeworkDetail(int $homeworkId);

    function getHomeworkIndex(int $userId,int $homeworkId);

    function getProblem(int $homeworkId,int $problemId);

//    function getHomeworkStatus(int $homeworkId,int $page,int $size,array $conditions = []);

    function getHomeworkRanklist(int $homeworkId);

    // 管理

    function createHomework(int $groupId,array $data,array $problems):int;

    function updateHomeworkInfo(int $homeworkId,array $data):bool;

    function updateHomeworkProblems(int $homeworkId,array $problems):bool;

    function deleteHomework(int $homeworkId):bool;

    // 提交

    function submitProblem(int $userId,int $homeworkId,int $problemNum,array $data):int;

    // 辅助

    function isHomeworkExist(int $homeworkId):bool;

    function canUserAccessHomework(int $userId,int $homeworkId):bool;
}