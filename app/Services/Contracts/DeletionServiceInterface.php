<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:20
 */

namespace NEUQOJ\Services\Contracts;

/**
 * Interface OperationLogInterface
 * 记录用户操作日志的模块 主要用于进行软删除的确认和撤销
 */
interface DeletionServiceInterface
{
    /**
     * 原子操作部分
     */
    function createDeletion(array $data):bool;

    public function createDeletions(array $data);

    function confirmDeletion(int $id):bool;

    function undoDeletion(int $id):bool;

    function getDeletion(int $id);

    function getLog(int $page,int $size);

    function getDeletionCount():int;

    /**
     *废弃

    function createLogItem(array $logItem,array $deletions):int;

    //分页获取所有log（根据时间倒序排序）
    function getLogs(int $page,int $size);

    //search
    function getLogsByUser(int $userId,int $page,int $size);

    function getLogItemById(int $gid);

    function confirmLogItem(int $gid):bool;

    function undoLogItem(int $gid):bool;
     *
     * */


}