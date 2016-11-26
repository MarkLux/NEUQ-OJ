<?php
Route::post('/createRole','RoleController@createRole');
Route::post('/deleteRole','RoleController@deleteRole');
Route::post('/giveRoleTo','RoleController@giveRoleTo');
Route::post('/test','RoleController@test');

/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-22
 * Time: 下午5:53
 */

Route::get('/test','UserGroup\TestController@test');