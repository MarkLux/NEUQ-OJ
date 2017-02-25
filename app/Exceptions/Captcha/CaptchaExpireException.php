<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/25
 * Time: 下午7:36
 */
namespace NEUQOJ\Exceptions\Captcha;
use NEUQOJ\Exceptions\BaseException;
class CaptchaExpireException extends BaseException
{
    protected $code = 4001;
    protected $data = 'the captcha is expired,please refresh the page';
}