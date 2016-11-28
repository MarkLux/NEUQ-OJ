<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:59
 */

namespace NEUQOJ\Services;


use Carbon\Carbon;
use Illuminate\Container\Container;
use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
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

    //根据传入字符返回实例
    private function getRepo(string $tableName):SoftDeletionInterface
    {
        $app = Container::getInstance();

        switch ($tableName)
        {
            case "UserGroup":
                $repoName = 'UserGroupRepository';
                break;
            case "UserGroupRelation":
                $repoName = 'UserGroupRelationRepository';
        }

        //如果想保证不出错建议做一个switch
        $class = "NEUQOJ\Repository\Eloquent\\".$repoName;
        return $app->make($class);
    }

    public function createDeletion(int $gid, array $data): bool
    {
        $current = new Carbon();

        $data['gid'] = $gid;

        $data['time'] = $current;

        return $this->userDeletionRepo->insert($data) == 1;
    }

    function confirmDeletion(int $id): bool
    {
        $deletion = $this->userDeletionRepo->get($id)->first();

//        dd($deletion);

        $repo = $this->getRepo($deletion->table_name);

        if(!$repo->doDeletion($deletion->key))
            return false;

        //clear the single deletion
        return $this->userDeletionRepo->deleteWhere(['id' => $id]);

    }

    function undoDeletion(int $id): bool
    {
        $deletion = $this->userDeletionRepo->get($id)->first();

        $repo = $this->getRepo($deletion->table_name);

        if(!$repo->undoDeletion($deletion->key))
            return false;

        //clear the single deletion
        return $this->userDeletionRepo->deleteWhere(['id' => $id]);
    }

    function getDeletion(int $opid)
    {
        return $this->userDeletionRepo->get($opid)->first();
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