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
    function get($id, array $columns = ['*'], $primary = 'id');
    function getBy($param, $value,array $columns = ['*']);
    function getByMult(array $params, array $columns = ['*']);
    function insert(array $data);
    function update(array $data, $id, $attribute="id");
    function delete($id);
}