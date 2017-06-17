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

    Route::get('/user-groups/joined','UserGroup\UserGroupController@getMyGroups');
    Route::get('/user-groups/created','UserGroup\UserGroupController@getCreatedGroups');

    Route::group(['prefix' => 'user-group'],function(){
        Route::post('/create','UserGroup\UserGroupController@createNewGroup');
        Route::get('/{id}','UserGroup\UserGroupController@getGroup');

        // 成员部分

        Route::get('/{id}/members','UserGroup\UserGroupController@getMembers');
        Route::post('/{id}/members/add','UserGroup\UserGroupController@addMembers');
        Route::post('/{id}/members/delete','UserGroup\UserGroupController@deleteMembers');
        Route::post('/{id}/members/update','UserGroup\UserGroupController@updateMemberInfo');
        Route::post('/{id}/members/update/mine','UserGroup\UserGroupController@updateMyTag');

        Route::post('/{id}/join','UserGroup\UserGroupController@joinGroup');
        Route::post('/{id}/quit','UserGroup\UserGroupController@quitGroup');
        Route::post('/{id}/change-owner','UserGroup\UserGroupController@changeOwner');
        Route::post('/{id}/dismiss','UserGroup\UserGroupController@dismissGroup');
        Route::post('/{id}/update','UserGroup\UserGroupController@updateGroupInfo');

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
