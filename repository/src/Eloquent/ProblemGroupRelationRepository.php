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
        return "NEUQOJ\Repository\Models\ProblemGroupRelation";
    }

    use SoftDeletionTrait;

    public function getRelationsByNums(int $groupId,array $problemNums,array $columns = ['*'])
    {
        return $this->model
            ->where('problem_group_id',$groupId)
            ->whereIn('problem_num',$problemNums)->get($columns);
    }

    public function getRelationsByIds(int $groupId,array $problemIds,array $columns =  ['*'])
    {
        return $this->model
            ->where('problem_group_id',$groupId)
            ->whereIn('problem_id',$problemIds)->get($columns);
    }

    public function getProblemInfosInGroup(int $groupId)
    {
        return $this->model
            ->where('problem_group_id',$groupId)
            ->join('problems','problem_group_relations.problem_id','=','problems.id')
            ->select('problem_group_relations.problem_num','problem_group_relations.problem_score', 'problems.id','problems.title',
                'problems.source')
            ->get();
    }

    public function deleteWhereIn(string $param,array $data)
    {
        return $this->model->whereIn($param,$data)->delete();
    }
}