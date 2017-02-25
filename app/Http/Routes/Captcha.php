<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/25
 * Time: 下午8:02
 */

Route::get('/mark/captcha/get','CaptchaController@getCaptcha');

Route::get('/user/register','CaptchaController@initCaptchaSession');