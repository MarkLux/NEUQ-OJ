<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-22
 * Time: 下午5:46
 */

Route::get('/user-group/search','UserGroup\UserGroupController@searchGroups');
Route::get('/user-groups','UserGroup\UserGroupController@getGroups');

Route::group(['middleware' => 'token'],function (){

    Route::get('/user/groups','UserGroup\UserGroupController@getGroupsUserIn');

    Route::group(['prefix' => 'user-group'],function(){
        Route::post('/create','UserGroup\UserGroupController@createNewGroup');
        Route::get('/{id}','UserGroup\UserGroupController@getIndex');
        Route::get('/{id}/members','UserGroup\UserGroupController@getMembers');
        Route::post('/{id}/join-in','UserGroup\UserGroupController@joinGroup');
        Route::post('/{id}/quit','UserGroup\UserGroupController@quitGroup');
        Route::post('{id}/change-owner','UserGroup\UserGroupController@changeOwner');
        Route::post('{id}/close','UserGroup\UserGroupController@closeGroup');
        Route::post('{id}/open','UserGroup\UserGroupController@openGroup');
        Route::post('{id}/dismiss','UserGroup\UserGroupController@dismissGroup');

        Route::group(['prefix' => 'notices'],function(){
            Route::post('/create','UserGroup\NoticeController@addNotice');
            Route::get('/show/{id}','UserGroup\NoticeController@getNotice');
            Route::get('/get','UserGroup\NoticeController@getGroupNotices');
            Route::post('/delete/{id}','UserGroup\NoticeController@deleteNotice');
            Route::post('/update/{id}','UserGroup\NoticeController@updateNotice');
        });

        Route::post('{id}/add-homework','HomeworkController@addHomework');
        Route::get('{id}/homeworks','HomeworkController@getHomeworks');
    });
});


//Route::group(['middleware' => 'token'],function(){
//    Route::resource('user-groups','UserGroupController',
//        ['only' => ['create','show','edit','update','destory']]);
//});
