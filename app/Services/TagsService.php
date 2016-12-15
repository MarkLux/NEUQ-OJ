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
use NEUQOJ\Services\Contracts\TagsServiceInterface;

class TagsService implements TagsServiceInterface
{
    public function createTags(string $name):int//返回tag的id
    {

    }

    public function deleteTags(int $id):bool
    {

    }

    public function updateTags(int $id,string $content):bool
    {

    }

    public function hasTags(int $tagsId,int $problemId):bool//判断某题目是否已经有某标签
    {

    }

    public function tagsExisted(string $name):int//判断某标签是否存在 返回tag的id
    {

    }

    public function giveTagsTo(int $tagsId,int $problemId):bool
    {

    }

    function updateProblemTag(int $OldTagId,int $problemId,string $content):bool //对题目已有标签编辑（除直接删除）统一入口
    {
        $tagId = $this->tagsExisted($content);

        if($tagId == -1)//说明修改后的内容tag表中不存在
        {
            DB::transation(
                function ()use($content,$tagId,$problemId,$OldTagId)
                {
                    $tagId = $this->createTags($content);//创建一个新的tag

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

    }


    function createProblemTag(int $problemId,string $content):bool//对题目添加标签
    {
        $tagId = $this->tagsExisted($content);


        if ($tagId == -1)//新增题目标签不在tag表中
        {
            DB::transation(
                function () use ($content, $tagId, $problemId) {
                    $tagId = $this->createTags($content);//创建一个新的tag

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
}