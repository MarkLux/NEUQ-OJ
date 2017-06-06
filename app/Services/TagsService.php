<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午8:32
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\Tag\TagsExistException;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use NEUQOJ\Repository\Eloquent\ProblemTagRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemTagRepository;
use NEUQOJ\Services\Contracts\TagsServiceInterface;

class TagsService implements TagsServiceInterface
{
    private $problemTagRepo;
    private $problemTagRelationRepo;
    private $problemService;
    private $problemRepo;

    public function __construct(ProblemTagRepository $problemTagRepository,
                                ProblemTagRelationRepository $problemTagRelationRepository,
                                ProblemService $problemService,ProblemRepository $problemRepository)
    {
        $this->problemRepo = $problemRepository;
        $this->problemTagRepo = $problemTagRepository;
        $this->problemTagRelationRepo = $problemTagRelationRepository;
        $this->problemService = $problemService;
    }

    public function createTags(array $data):int//同时插入多个标签，有相同的跳过
    {
//        //判断创建的tag是否存在
        $insert = [];
       $tags = $this->problemTagRepo->all(['name']);
       foreach ($data as $datum){
           $flag = 0;
           foreach ($tags as $tag){
               if ($tag->name == $datum){
                   $flag = 1;
                   break;
               }
           }
           if (!$flag){
               $insert[] = [
                   'name'=>$datum
               ];
           }
       }
        if ($insert == []){return 0;}
        else{

            $id  = -1;
            DB::transaction(
                function ()use($id,$insert)
                {
                    $this->problemTagRepo->insert($insert);
                }
            );
            return true;
        }

    }

    public function deleteTag(int $id):bool//只能一次删除一个，并且所有的关系也会被删除
    {
        DB::transaction(
            function ()use($id)
            {
                $this->problemTagRepo->deleteWhere(['id'=>$id]);
                $this->problemTagRelationRepo->deleteWhere(['tag_id'=>$id]);
            }
        );
        return true;
    }

    public function updateTag(int $id,string $content):bool//一次只能修改一个标签
    {
        DB::transaction(
          function ()use($id,$content)
          {
              $this->problemTagRepo->updateWhere(['id'=>$id],['name'=>$content]);
          }
        );

        return true;
    }

    public function hasTag(int $tagsId,int $problemId):bool//判断某题目是否已经有某标签
    {

        $arr = $this->problemTagRelationRepo->getByMult(['problem_id'=>$problemId,'tag_id'=>$tagsId],['tag_id']);
        if ($arr != null)
            return true;
        return false;
    }

    public function tagsExisted(string $name):int//判断某标签是否存在 返回tag的id 不存在返回-1
    {


    }

    public function giveTagTo(int $tagId,int $problemId):bool
    {
        $data = array(
          'problem_id'=>$problemId,
          'tag_id'=>$tagId,
        );
        return $this->problemTagRelationRepo->insert($data);
    }

    function updateProblemTag(int $OldTagId,int $problemId,string $content):bool //直接修改关系
    {
        //先看修改后的content是否存在，如果不存在，先创建此tag,然后把关系修改
        //如果存在，先检查新换的id是否跟这个题目存在关系了，如果不存在的话，更新原关系
        //否则抛出异常
        $temp = $this->problemTagRepo->getBy('name',$content,['id']);
        $data = $temp->toArray();
        $tagId = -1;

        if ($data == null)
        {

            DB::transaction(
                function () use ($content,$OldTagId,$problemId)
                {

                    $NewTagId = $this->problemTagRepo->insertWithId(['name'=>$content]);
                    $this->problemTagRelationRepo->updateWhere(['tag_id'=>$OldTagId,'problem_id'=>$problemId],['tag_id'=>$NewTagId]);
                }
            );
            return true;
        }
        else
        {

            foreach ($data as $datum)
            $NewTagId = $datum['id'];
            if($this->hasTag($NewTagId,$problemId))
                throw new TagsExistException();
            else
             return $this->problemTagRelationRepo->updateWhere(['tag_id'=>$NewTagId,'problem_id'=>$problemId],['tag_id'=>$tagId]);
        }


    }

    function deleteProblemTag(int $tagId,int $problemId):bool//直接删除对应题目的对应标签
    {
        return $this->problemTagRelationRepo->deleteWhere(['tag_id'=>$tagId,'problem_id'=>$problemId]);

    }

    public function getTagById(int $tagId,array $columns=['*'])
    {
        return $this->problemTagRepo->get($tagId,$columns)->first();
    }

    public function getTagByName(string $name,array $columns=['*'])
    {
        return $this->problemTagRepo->getBy('name',$name)->first();
    }

    public function getSameTagProblemList(int $tagId,int $page,int $size)
    {
        return $this->problemTagRelationRepo->paginate($page,$size,['tag_id'=>$tagId],['problem_id','problem_title','tag_title','tag_id']);
    }

    public function getSameSourceProblemList(string $Source, int $page, int $size)
    {
        return $this->problemRepo->paginate($page,$size,['source'=>$Source],['problem_id','problem_title','source','submit','accepted'
            ,'is_public','created_at','tags']);
    }
}