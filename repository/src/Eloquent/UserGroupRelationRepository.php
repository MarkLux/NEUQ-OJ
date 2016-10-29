<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:58
 */

namespace NEUQOJ\Repository\Eloquent;


class UserGroupRelationRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserGroupRelation";
    }

    function updateWhere(array $condition,array $data)
    {
        if($this->model->timestamps){
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
            ->where($condition)
            ->update($data);
    }
}