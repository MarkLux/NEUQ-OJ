<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-28
 * Time: 下午2:43
 */

namespace NEUQOJ\Repository\Contracts;


interface SoftDeletionInterface
{
    function doDeletion(int $id):bool;

    function undoDeletion(int $id):bool;
}