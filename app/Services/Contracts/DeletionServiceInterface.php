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
    function createDeletion(int $gid,array $data):bool;

    function confirmDeletion(int $opid):bool;

    function undoDeletion(int $opid):bool;

    function getDeletion(int $opid);

    /**
     * 组操作
     */

    function createLogItem(array $data):bool;

    //分页获取所有log（根据时间倒序排序）
    function getLogs(int $page,int $size);

    //search
    function getLogsByUser(string $name,int $page,int $size);

    function getLogItemById(int $gid);

    function confirmLogItem(int $gid);

    function undoLogItem(int $gid);

}