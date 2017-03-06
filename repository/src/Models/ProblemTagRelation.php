<?php

namespace NEUQOJ\Repository\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProblemTagRelation extends Model
{
    protected $table = 'problem_tag_relations';
    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
