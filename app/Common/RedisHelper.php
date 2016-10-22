<?php
/**
 * Created by PhpStorm.
 * User: trons
 * Date: 16/10/12
 * Time: 下午9:02
 */

namespace NEUQOJ\Common;


use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    public static function command($comm, $key, $data = [], $nret = true){
        $val[] = $key;
        if(!empty($data)){
            $val = array_merge($val, $data);
        }
        $ret =  Redis::command($comm, $val);
        if(!$nret)
            return 1;
        $res = [];
        foreach ($data as $key => $item){
            $res[$data[$key]] = $ret[$key];
        }
        return $res;
    }
}