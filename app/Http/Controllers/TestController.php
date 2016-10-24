<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-23
 * Time: 下午8:42
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        $modeStr = '/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/';
        $subStr = 'markfj163.com';
        dd(preg_match($modeStr,$subStr));
    }
}