<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/4/19
 * Time: 下午10:30
 */

namespace NEUQOJ\Services\Contracts;


interface FreeProblemSetServiceInterface
{
    function importProblems($file,array $config);

    function exportProblems(array $problemIds);
}