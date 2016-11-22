<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-22
 * Time: 下午5:46
 */

Route::get('/user-group/search','UserGroupController@searchGroups');

Route::group(['middleware' => 'token'],function (){

    Route::group(['prefix' => 'user-group'],function(){
        Route::post('/create','UserGroupController@createNewGroup');
        Route::get('/{id}','UserGroupController@getIndex');
        Route::get('/{id}/members','UserGroupController@getMembers');
        Route::post('/{id}/join-in','UserGroupController@joinGroup');
        Route::post('/{id}/quit','UserGroupController@quitGroup');
    });
});

//Route::group(['middleware' => 'token'],function(){
//    Route::resource('user-groups','UserGroupController',
//        ['only' => ['create','show','edit','update','destory']]);
//});
