<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午4:11
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Services\Contracts\SolutionService;

class SolutionController extends Controller
{
    private $solutionService;

    public function __construct(SolutionService $service)
    {
        $this->solutionService = $service;
    }

    public function getSolutions(Request $request)
    {

    }
}