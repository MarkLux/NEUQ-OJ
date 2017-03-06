<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/25
 * Time: 下午8:23
 */
namespace NEUQOJ\Exceptions\Captcha;
use NEUQOJ\Exceptions\BaseException;
class CaptchaNotMatchException extends BaseException
{
    protected $code = 4002;
    protected $data = 'the captcha you input is not match with the picture';
}