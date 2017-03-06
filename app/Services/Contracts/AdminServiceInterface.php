<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/24
 * Time: 下午9:04
 */

namespace NEUQOJ\Services\Contracts;

interface AdminServiceInterface
{
    //利用一个前缀批量生成竞赛、考试账号
    function generateUsersByPrefix(string $prefix,int $num,array $names=[]);
}