<?php
namespace NEUQOJ\Common;

use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\ProblemGroup\LanguageErrorException;

/**
 * Created by PhpStorm.
 * User: trons
 * Date: 16/10/12
 * Time: 下午8:49
 */
class Utils
{

    static function createTimeStamp(): float
    {
        list($micro, $se) = explode(' ', microtime());
        return $se * 1000 + round($micro * 1000, 0);
    }

    public static function isEmailAvailable(string $email): bool
    {
        $pattern = '/\w[-\w.+]*@neuqoj.com/';
        return !(preg_match($pattern, $email) == 1);
    }

    public static function IsEmail(string $str): bool
    {
        $patternEmail = '/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/';
        return preg_match($patternEmail, $str) == 1;
    }

    public static function IsMobile(string $str): bool
    {
        //$patternMobile = '/(13\d|14[57]|15[^4,\D]|17[0123456789]|18\d)\d{8}|170[059]\d{7}/';
	$patternMobile = '/^1[345678]\d{9}$/';        
	return preg_match($patternMobile, $str) == 1;
    }

    //竞赛排行榜排序函数
    public static function rankCmpObj($A, $B)
    {
        if ($A->solved != $B->solved)
            return $A->solved < $B->solved;
        else
            return $A->time > $B->time;
    }

    public static function rankCmpArr($A, $B)
    {
        if ($A['solved'] != $B['solved'])
            return $A['solved'] < $B['solved'];
        else
            return $A['time'] > $B['time'];
    }

    public static function scoreCmpObj($A, $B)
    {
        if ($A->score != $B->score)
            return $A->score < $B->score;
        else
            return $A->time > $B->time;
    }

    public static function scoreCmpArr($A, $B)
    {
        if ($A['score'] != $B['score'])
            return $A['score'] < $B['score'];
        else
            return $A['time'] > $B['time'];
    }

    //原版oj加密解密系统
    public static function pwGen($password, $md5ed = False)
    {
        if (!$md5ed) $password = md5($password);
        $salt = sha1(rand());
        $salt = substr($salt, 0, 4);
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

    public static function pwCheck($password, $saved)
    {
        if (Utils::isOldPW($saved)) {
            $mpw = md5($password);
            if ($mpw == $saved) return True;
            else return False;
        }
        $svd = base64_decode($saved);
        $salt = substr($svd, 20);
        $hash = base64_encode(sha1(md5($password) . $salt, true) . $salt);
        if (strcmp($hash, $saved) == 0) return True;
        else return False;
    }

    private static function isOldPW($password)
    {
        for ($i = strlen($password) - 1; $i >= 0; $i--) {
            $c = $password[$i];
            if ('0' <= $c && $c <= '9') continue;
            if ('a' <= $c && $c <= 'f') continue;
            if ('A' <= $c && $c <= 'F') continue;
            return False;
        }
        return True;
    }

    public static function getProblemDataPath(int $problemId): string
    {
        return config('judger.testcases_path') .'/'.$problemId;
//        return '/Users/mark/TestCases/'.$problemId;
    }

    public static function validateCheck(array $inputs,array $rules)
    {
        $validator = Validator::make($inputs,$rules);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }
    }

    public static function switchLanguage(int $langCode)
    {
        switch ($langCode) {
            case 0:
                return 'c';
            case 1:
                return 'cpp';
            case 2:
                return 'java';
            case 3:
                return 'py2';
            default:
                throw new LanguageErrorException();
        }
    }
}
