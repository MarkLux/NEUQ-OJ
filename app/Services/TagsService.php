<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午8:32
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\TagsUnchangedExceptions;
use NEUQOJ\Repository\Eloquent\ProblemTagRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemTagRepository;
use NEUQOJ\Services\Contracts\TagsServiceInterface;

class TagsService implements TagsServiceInterface
{
    private $problemTagRepo;
    private $problemTagRelationRepo;
    private $problemService;

    public function __construct(ProblemTagRepository $problemTagRepository,ProblemTagRelationRepository $problemTagRelationRepository,ProblemService $problemService)
    {
        $this->problemTagRepo = $problemTagRepository;
        $this->problemTagRelationRepo = $problemTagRelationRepository;
        $this->problemService = $problemService;
    }

    public function createTags(array $data):int//返回tag的id
    {
        return $this->problemTagRepo->insertWithId($data);
    }

    public function deleteTags(int $id):bool
    {
        DB::transation(
            function ()use($id)
            {
                $this->problemTagRepo->deleteWhere(['id'=>$id]);
                $this->problemTagRelationRepo->deleteWhere(['tag_id'=>$id]);
            }
        );


    }

    public function updateTags(int $id,string $content):bool//如果哪些题目用了这个标签也一并被修改
    {
        DB::transation(
          function ()use($id,$content)
          {
              $this->problemTagRepo->updateWhere(['id'=>$id],['name'=>$content]);
              $this->problemTagRelationRepo->updateWhere(['tag_id'=>$id],['tag_title'=>$content]);
          }
        );

        return true;
    }

    public function hasTags(int $tagsId,int $problemId):bool//判断某题目是否已经有某标签
    {
        //提出关系表中该题目的所有标签
        $arr = $this->problemTagRelationRepo->getBy('problem_id',$problemId);
        foreach ($arr as $item)//匹配到对应标签表示该题目有该标签
        {
            if($item['tag_id'] == $tagsId)
                return true;
        }

        return false;
    }

    public function tagsExisted(string $name):int//判断某标签是否存在 返回tag的id 不存在返回-1
    {
        $tagId = -1;
        $arr = $this->problemTagRepo->getBy('name',$name)->first();
        if($arr != null)
            $tagId = $arr['id'];

        return $tagId;
    }

    public function giveTagsTo(int $tagsId,int $problemId):bool
    {
        $problem = $this->problemService->getProblemById($problemId);

        $tag = $this->getTagById($tagsId);

        $data = array(
          'problem_id'=>$problemId,
          'tag_id'=>$tagsId,
          'tag_title'=>$tag['name'],
          'problem_title'=>$problem['title']
        );

        return $this->problemTagRepo->insert($data);
    }

    function updateProblemTag(int $OldTagId,int $problemId,string $content):bool //对题目已有标签编辑（除直接删除）统一入口
    {
        $tagId = $this->tagsExisted($content);

        if($tagId == -1)//说明修改后的内容tag表中不存在
        {
            DB::transation(
                function ()use($content,$tagId,$problemId,$OldTagId)
                {
                    $data = array(
                        'name'=>$content
                    );

                    $tagId = $this->createTags($data);//创建一个新的tag

                    $this->giveTagsTo($tagId,$problemId);//赋予新的标签

                    $this->deleteProblemTag($OldTagId,$problemId);//删除原有的关系
                }
            );

        }

        else
            if ($tagId = $OldTagId)//修改后的内容tag表中存在且等于现在有的这个标签的标号
                throw new TagsUnchangedExceptions();
            else
            {
                DB::transation(
                    function ()use($tagId,$problemId,$OldTagId)
                    {
                        $this->giveTagsTo($tagId,$problemId);//赋予新的标签

                        $this->deleteProblemTag($OldTagId,$problemId);//删除原有的关系
                    }
                );
            }

        return true;
    }

    function deleteProblemTag(int $tagId,int $problemId):bool//直接删除对应题目的对应标签
    {
        $this->problemTagRelationRepo->deleteWhere(['tag_id'=>$tagId,'problem_id'=>$problemId]);
    }


    function createProblemTag(int $problemId,string $content):bool//对题目添加标签
    {
        $tagId = $this->tagsExisted($content);


        if ($tagId == -1)//新增题目标签不在tag表中
        {
            DB::transation(
                function () use ($content, $tagId, $problemId) {

                    $data = array(
                        'name'=>$content
                    );
                    $tagId = $this->createTags($data);//创建一个新的tag

                    $this->giveTagsTo($tagId, $problemId);//赋予新的标签

                }
            );
        }

        else
            if (!($this->giveTagsTo($tagId,$problemId)))
                return false;

            else return true;

        return true;
    }

    public function getTagById(int $tagId):array
    {
        return $this->problemTagRepo->get($tagId)->first();
    }

    public function getTagByName(string $name):array
    {
        return $this->problemTagRepo->getBy('name',$name)->first();
    }
}