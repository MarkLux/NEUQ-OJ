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
use Illuminate\Support\Facades\DB;
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

    public function __construct(UserDeletionRepository $userDeletionRepository)
    {
        $this->userDeletionRepo = $userDeletionRepository;
    }

    //根据传入字符返回仓库实例
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
                break;
        }

        //如果想保证不出错建议做一个switch
        $class = "NEUQOJ\Repository\Eloquent\\".$repoName;
        return $app->make($class);
    }

    function getLog(int $page,int $size)
    {
        return $this->userDeletionRepo->paginate($page,$size);
    }

    /**
     * 单条删除记录部分
     */
    public function createDeletion(array $data): bool
    {
        return $this->userDeletionRepo->insert($data) == 1;
    }

    public function createDeletions(array $data)
    {
        return $this->userDeletionRepo->insert($data);
    }

    function confirmDeletion(int $id): bool
    {
        $deletion = $this->userDeletionRepo->get($id)->first();

        $repo = $this->getRepo($deletion->table_name);

        if(!$repo->doDeletion($deletion->key))
            return false;

        //clear the single deletion
        return $this->userDeletionRepo->deleteWhere(['id' => $id])==1;

    }

    function undoDeletion(int $id): bool
    {
        $deletion = $this->userDeletionRepo->get($id)->first();

        $repo = $this->getRepo($deletion->table_name);

        if(!$repo->undoDeletion($deletion->key))
            return false;

        //clear the single deletion
        return $this->userDeletionRepo->deleteWhere(['id' => $id])==1;
    }

    function getDeletion(int $opid)
    {
        return $this->userDeletionRepo->get($opid)->first();
    }

    function getDeletionCount(): int
    {
        return $this->userDeletionRepo->getCount();
    }

    //因为复杂度和效率原因废弃了对删除行为制作分组日志的功能

/*

    function getLogs(int $page, int $size)
    {
        return $this->deletionLogRepo->paginate($page,$size);
    }

    function getLogItemById(int $gid)
    {
        return $this->deletionLogRepo->get($gid)->first();
    }

    function getLogsByUser(int $userId, int $page, int $size)
    {
        return $this->deletionLogRepo->paginate($page,$size,[
            'user_id' => $userId
        ]);
    }

    function createLogItem(array $logItem,array $deletions): int
    {

        $gid = -1;
        //内部开启事务处理方式
        DB::transaction(function() use ($gid,$logItem,$deletions){
            $gid =$this->deletionLogRepo->insertWithId($logItem);
            foreach($deletions as $deletion)
            {
                $deletion['gid'] = $gid;
            }
            $this->userDeletionRepo->insert($deletions);
        });

        return $gid;//nice idea!
    }

    function confirmLogItem(int $gid):bool
    {
        //确认一次记录所有项目的删除
        $deletions = $this->userDeletionRepo->getBy('gid',$gid);

        //虽然循环这样删除效率低下，但是保证逻辑的简单性
        //若是要考虑性能最好的办法还是先根据表分类，再在每个表中执行批量处理
        DB::transaction(function() use ($deletions){
            foreach ($deletions as $deletion)
            {

            }
        });
    }

    function undoLogItem(int $gid):bool
    {
    }

*/

}