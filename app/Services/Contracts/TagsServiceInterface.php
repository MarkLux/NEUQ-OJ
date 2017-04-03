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
    function createTags(array $data):int;//返回tag的id

    function deleteTag(int $id):bool;//删除tag表中的字段

    function updateTag(int $id,string $content):bool;//更新tag表中的字段

    function hasTag(int $tagsId,int $problemId):bool;//判断某题目是否已经有某标签

    function tagsExisted(string $name):int;//判断某标签是否存在 返回tag的id

    function giveTagTo(int $tagId,int $problemId):bool;//直接提取tag表中已有的标签赋予给题目

    function updateProblemTag(int $tagId,int $problemId,string $content):bool;//对题目已有标签编辑（除直接删除）统一入口

    function deleteProblemTag(int $tagId,int $problemId):bool;//直接删除对应题目的对应标签

    function getTagById(int $tagId,array $columns=['*']);

    function getTagByName(string $name,array $columns=['*']);

    function getSameTagProblemList(int $tagId,int $page,int $size);//获取标签相同的题目

    function getSameSourceProblemList(string $Source,int $page,int $size);//获取相同来源的题目
}