<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-28
 * Time: 下午5:03
 */

namespace NEUQOJ\Http\Controllers\Admin;


use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Services\DeletionService;

class DeletionLogController extends Controller
{

    private $deletionService;

    public function __construct(DeletionService $deletionService)
    {
        $this->deletionService = $deletionService;
    }

    public function doDeletion(int $opid)
    {
        if($this->deletionService->confirmDeletion($opid))
            return "HaHa";
    }

    public function undoDeletion(int $opid)
    {
        if($this->deletionService->undoDeletion($opid))
            return "HaHa";
    }
}