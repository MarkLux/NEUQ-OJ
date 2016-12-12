<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-12
 * Time: 下午4:41
 */

namespace NEUQOJ\Services;


use NEUQOJ\Services\Contracts\ProblemServiceInterface;
use Illuminate\Support\Facades\File;
use NEUQOJ\Repository\Eloquent\ProblemRepository;

class ProblemService implements ProblemServiceInterface
{

    private $problemRepo;

    function __construct(ProblemRepository $problemRepository)
    {
        $this->problemRepo = $problemRepository;
    }

    /**
     * 添加题目
     */
    function addProblem(array $data)
    {

    }
}