<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:15
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class ProblemGroupRelationRepository extends AbstractRepository implements SoftDeletionInterface
{
    public function model()
    {
        return "NEUQOJ\Models\ProblemGroupRelation";
    }

    use SoftDeletionTrait;

    public function getRelationsByNums(int $groupId,array $problemNums,array $columns = ['*'])
    {
        return $this->model
            ->where('problem_group_id',$groupId)
            ->whereIn('problem_num',$problemNums)->get($columns);
    }

    public function deleteWhereIn(string $param,array $data)
    {
        return $this->model->whereIn($param,$data)->delete();
    }
}