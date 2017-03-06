<?php
namespace NEUQOJ\Repository\Contracts;

/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-11
 * Time: 下午8:44
 */
interface RepositoryInterface
{
    function all(array $columns = ['*']):array;

    function get(int $id, array $columns = ['*'], string $primary = 'id'):array;

    function getBy(string $param, string $value, array $columns = ['*']):array;

    function getByMult(array $params, array $columns = ['*']):array;

    function insert(array $data):bool;

    function update(array $data, int $id, string $attribute = "id"):int;

    function delete(int $id):int;

    function paginate(int $page = 1, int $size = 15, array $params = [], array $columns = ['*']):array;
}