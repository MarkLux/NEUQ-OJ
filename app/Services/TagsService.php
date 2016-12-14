<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午8:32
 */

namespace NEUQOJ\Services;


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

    function updateProblemTag(int $OldTagId,int $problemId)//更新题目的某个标签
    {

    }

    function deleteProblemTags(int $tagId,int $problemId):bool//删除对应题目的对应标签
    {

    }
}