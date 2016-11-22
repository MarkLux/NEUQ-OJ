<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-22
 * Time: 下午5:51
 */

Route::post('/register','AuthController@register');

Route::post('/login','AuthController@login');

Route::group(['middleware' => 'token'],function(){
    Route::get('/logout','AuthController@logout');
});