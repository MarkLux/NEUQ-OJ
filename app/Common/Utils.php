<?php
namespace NEUQOJ\Common;
/**
 * Created by PhpStorm.
 * User: trons
 * Date: 16/10/12
 * Time: 下午8:49
 */
class Utils
{
    static function createTimeStamp(){
        list($micro, $se) = explode(' ', microtime());
        return $se * 1000 + round($micro, 0);
    }
}