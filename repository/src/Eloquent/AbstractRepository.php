<?php
namespace NEUQOJ\Repository\Eloquent;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use NEUQOJ\Repository\Contracts\RepositoryInterface;

/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-11
 * Time: 下午9:39
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /** @var Model $model */
    private $model;

    function __construct(Container $app)
    {
        $model = $app->make($this->model());
        $this->model = $model->newQuery();
    }

    abstract function model();

    function all(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    function get($id, array $columns = ['*'], $primary = 'id')
    {
        return $this->model
            ->where($primary, $id)
            ->get($columns);
    }

    function getBy($param, $value,array $columns = ['*']){
        return $this->model
            ->where($param, $value)
            ->get($columns);
    }

    function getByMult(array $params, array $columns = ['*']){
        return $this->model
            ->where($params)
            ->get($columns);
    }

    function insert(array $data)
    {
        return $this->model
            ->insert($data);
    }

    function update(array $data, $id, $attribute="id")
    {
        return $this->model
            ->where($attribute, '=', $id)
            ->update($data);
    }

    function delete($id)
    {
        return $this->model
            ->destory($id);
    }

    function paginate($page = 1, $size = 15, $param = [], $columns = ['*'])
    {
        $qb = $this->model;
        if(!empty($size))
            $qb->where($param);
        return $qb
            ->skip($size * --$page)
            ->take($size)
            ->get($columns);

    }
}