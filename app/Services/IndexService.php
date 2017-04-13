<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/4/13
 * Time: 上午12:09
 */


namespace NEUQOJ\Services;
use NEUQOJ\Repository\Eloquent\SolutionRepository;

/**
 * 用于首页一些信息的组织
 */

class IndexService
{
    private $solutionRepo;

    public function __construct(SolutionRepository $repository)
    {
        $this->solutionRepo = $repository;
    }

    public function getSubmitStatics()
    {
        // todo 完善
    }
}