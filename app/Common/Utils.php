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
    const SALT = 'bX7av6HhjvI5rifNhOiJ';

    public static function encryption($password){
        return md5(self::SALT.$password);
    }

    static function createTimeStamp(){
        list($micro, $se) = explode(' ', microtime());
        return $se * 1000 + round($micro, 0);
    }

}