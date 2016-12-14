<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午7:08
 */

namespace NEUQOJ\Services\Contracts;


interface TagsServiceInterface
{
    function createTags(string $name):int;//返回tag的id

    function deleteTags(int $id):bool;

    function updateTags(int $id,string $content):bool;//更新tag表中的字段

    function hasTags(int $tagsId,int $problemId):bool;//判断某题目是否已经有某标签

    function tagsExisted(string $name):int;//判断某标签是否存在 返回tag的id

    function giveTagsTo(int $tagsId,int $problemId):bool;

    function updateProblemTag(int $tagId,int $problemId):bool;//更新题目的某个标签

    function deleteProblemTags(int $tagId,int $problemId):bool;//删除对应题目的对应标签

}