<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/9/1
 * Time: 上午9:19
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\File;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Repository\Eloquent\ProblemRepository;

class RunDataService
{
    private $problemRepo;

    public function __construct(ProblemRepository $problemRepository)
    {
        $this->problemRepo = $problemRepository;
    }

    public function getRunDataList(int $problemId)
    {
        $dirPath = Utils::getProblemDataPath($problemId);

        if (!File::isDirectory($dirPath)) {
            throw new InnerError("Fail to get Problem Data");
        }

        return  File::files($dirPath);
    }


}