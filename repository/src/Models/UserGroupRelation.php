<?php

namespace NEUQOJ\Repository\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroupRelation extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
}

