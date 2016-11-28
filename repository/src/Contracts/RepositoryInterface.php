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
    function all(array $columns = ['*']);

    function get(int $id, array $columns = ['*'], string $primary = 'id');

    function getBy(string $param, string $value, array $columns = ['*']);

    function getByMult(array $params, array $columns = ['*']);

    function insert(array $data);

    function update(array $data, int $id, string $attribute = "id");

    function delete(int $id);

    function paginate(int $page = 1, int $size = 15, array $params = [], array $columns = ['*']);

    //Abstract Repo实现了这个接口 但是同时也进行了扩充 TODO 考虑是否要更改接口与实现一致
}