<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-17
 * Time: 下午5:15
 */

namespace NEUQOJ\Services\Contracts;

interface SolutionServiceInterface
{
    function getAllSolutions(int $page,int $size);

    function getSolutionBy(string $param,$value);

    function getSolution(int $solutionId);

}