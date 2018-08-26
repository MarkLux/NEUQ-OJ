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
        Route::post('/password/change','AdminController@changeUserPassword');
    });

    Route::post('/news/create','NewsController@addNews');
    Route::post('/news/{id}/update','NewsController@updateNews');
    Route::get('/news/{id}/delete','NewsController@deleteNews');

    Route::post('/problems/import','AdminController@importProblems');
    Route::post('/problems/export','AdminController@exportProblems');

    Route::post('/admin/users/generate/prefix','AdminController@generateUsersByPrefix');
});


Route::get('/hantest','TestController@fixBigBug');