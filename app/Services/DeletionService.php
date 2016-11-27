<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:59
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\DeletionLogRepository;
use NEUQOJ\Repository\Eloquent\UserDeletionRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRepository;
use NEUQOJ\Repository\Models\UserDeletion;
use NEUQOJ\Services\Contracts\DeletionServiceInterface;

class DeletionService implements DeletionServiceInterface
{
    /**
    *原子操作
    */

    private $userDeletionRepo;
    private $DeletionLogRepo;

    public function __construct(
        UserDeletionRepository $userDeletionRepository,DeletionLogRepository $deletionLogRepository
    )
    {
        $this->userDeletionRepo = $userDeletionRepository;
        $this->DeletionLogRepo = $deletionLogRepository;
    }

    private function getRepo(string $tableName)
    {
        switch ($tableName)
        {
            //how to fill this to adapt
        }
    }

    public function createDeletion(int $gid, array $data): bool
    {
        $data['gid'] = $gid;

        return $this->userDeletionRepo->insert($data) == 1;
    }

    function confirmDeletion(int $opid): bool
    {
        // TODO: Implement confirmOperation() method.
    }

    function undoDeletion(int $opid): bool
    {
        // TODO: Implement undoOperation() method.
    }

    function getDeletion(int $opid)
    {
        // TODO: Implement getOperation() method.
    }

    function getLogs(int $page, int $size)
    {
        // TODO: Implement getLogs() method.
    }

    function getLogItemById(int $gid)
    {
        // TODO: Implement getLogItemById() method.
    }

    function getLogsByUser(string $name, int $page, int $size)
    {
        // TODO: Implement getLogsByUser() method.
    }

    function createLogItem(array $data): bool
    {
        // TODO: Implement createLogItem() method.
    }
    function confirmLogItem(int $gid)
    {
        // TODO: Implement doLogItem() method.
    }

    function undoLogItem(int $gid)
    {
        // TODO: Implement undoLogItem() method.
    }



}