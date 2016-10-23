<?php
namespace NEUQOJ\Repository\Eloquent;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use NEUQOJ\Repository\Contracts\RepositoryInterface;
use NEUQOJ\Repository\Models\News;

/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-10-11
 * Time: 下午9:39
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /** @var Builder $model */
    private $model;

    protected $timestamps = true;

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
        if($this->timestamps){
            $current = new Carbon();

            if(! is_array(reset($data))){
                $data = array_merge($data,
                    [
                        'created_at' => $current,
                        'updated_at' => $current,
                    ]);
            }else{
                foreach ($data as  $key => $value) {
                    $data[$key] = array_merge($value,
                        [
                            'created_at' => $current,
                            'updated_at' => $current,
                        ]);
                }
            }

        }
        return $this->model
            ->insert($data);
    }

    function update(array $data, $id, $attribute="id")
    {
        if($this->timestamps){
            $current = new Carbon();

            if(! is_array(reset($data))){
                $data = array_merge($data,
                    [
                        'updated_at' => $current,
                    ]);
            }else{
                foreach ($data as  $key => $value) {
                    $data[$key] = array_merge($value,
                        [
                            'updated_at' => $current,
                        ]);
                }
            }

        }
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

    private function freshTimestamp()
    {
        return new Carbon;
    }
}