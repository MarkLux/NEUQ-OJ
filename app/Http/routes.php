<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return 'here is the main page!!';
});

Route::post('/register','AuthController@register');

Route::post('/login','AuthController@login');

Route::get('/user-group/search','UserGroupController@searchGroups');

Route::group(['middleware' => 'token'],function (){
    Route::get('/logout','AuthController@logout');
    Route::group(['prefix' => 'user-group'],function(){
        Route::post('/create','UserGroupController@createNewGroup');
        Route::get('/{id}','UserGroupController@getIndex');
        Route::get('/{id}/members','UserGroupController@getMembers');
        Route::post('/{id}/join-in','UserGroupController@joinGroup');

    });
});