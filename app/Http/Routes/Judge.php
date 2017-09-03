<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/23
 * Time: 上午11:29
 */

Route::group(['prefix' => 'judge'],function (){
    Route::group(['prefix' => 'server'],function(){
        Route::get('/','JudgeController@index');
        Route::post('/','JudgeController@addServer');
        Route::get('/all','JudgeController@getAll');
        Route::get('/{id}/info','JudgeController@getServerInfo');
        Route::get('/{id}','JudgeController@getServer');
        Route::post('/{serverId}/update','JudgeController@updateServer');
        Route::get('/{serverId}/delete','JudgeController@deleteServer');
    });
});