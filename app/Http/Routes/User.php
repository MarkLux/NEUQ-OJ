<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/23
 * Time: 下午7:38
 */



Route::post('/user/register','UserController@register');

Route::post('/user/login','UserController@login');

Route::get('/user/active','UserController@active');

Route::get('/user/active-mail/send','UserController@resendActiveMail');

Route::group(['middleware' => 'token'], function() {

    Route::group(['prefix' => 'user'], function() {
        Route::get('/info','UserController@getUserInfo');
        Route::get('/logout/', 'UserController@logout');
        Route::post('/getinfo', 'UserController@getUser');
        Route::post('/getinfos', 'UserController@getUsers');
        Route::post('/update', 'UserController@updateUser');
    });
});