<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/28
 * Time: 下午9:37
 */

Route::group(['middleware' => 'token'], function () {
    Route::group([
        'middleware' => 'admin',
        'prefix' => 'admin',
    ], function () {
        Route::get('/lock/{id}', 'AdminController@lockUser');
        Route::get('/unlock/{id}', 'AdminController@unlockUser');
    });
});