<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-20
 * Time: 下午10:36
 */

namespace NEUQOJ\Http\Controllers;


use NEUQOJ\Repository\Eloquent\UserDeletionRepository;
use Illuminate\Container\Container;

class TestController extends Controller
{
    public function test(Container $app)
    {
        $tableName = 'fu';
        $class = " NEUQOJ\Repository\Eloquent\\".$tableName;
        dd($class);
    }
}