<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/23
 * Time: 下午7:38
 */



Route::post('/user/register','UserController@register');
Route::get('/user/register','UserController@getCaptcha');

Route::post('/user/login','UserController@login');

Route::get('/user/active','UserController@active');

Route::post('/user/forgot-password','UserController@sendForgotPasswordEmail');

Route::post('/user/reset-password/verify','UserController@resetPasswordByVerifyCode');

Route::get('/user/active-mail/send','UserController@resendActiveMail');

Route::get('/user/{id}/info','UserController@getUserInfo');

Route::group(['middleware' => 'token'], function() {
    Route::get('/token-verify',function (){
        return response()->json([
            'code' => 0
        ]);
    });

    Route::group(['prefix' => 'user'], function() {
        Route::get('/me','UserController@getCurrentUserInfo');
        Route::get('/logout/', 'UserController@logout');
//        Route::post('/getinfo', 'UserController@getUser');
//        Route::post('/getinfos', 'UserController@getUsers');
        Route::post('/update', 'UserController@updateUser');
        Route::post('/reset-password','UserController@resetPasswordByOld');
    });
});