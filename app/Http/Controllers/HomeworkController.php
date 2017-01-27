<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17-1-25
 * Time: 下午3:08
 */

namespace NEUQOJ\Http\Controllers;


use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Services\HomeworkService;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    private $homeworkService;
    private $userGroupService;

    public function  __construct(HomeworkService $homeworkService,UserGroupService $userGroupService)
    {
        $this->homeworkService = $homeworkService;
        $this->userGroupService=$userGroupService;
    }

}
