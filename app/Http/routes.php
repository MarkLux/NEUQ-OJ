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

Route::group(['middleware' => 'token'],function (){
    Route::get('/profile',function (){
        return "hello";
    });

    Route::get('/test',['middleware' => 'privilege:check,create-post',function (){
        echo "You Have The Right!";
    }]);
    Route::post('/applyTeacher','PrivilegeController@applyTeacher');
    Route::post('/getApply','ApplyController@getApply');
    Route::post('/confirmRoleApply','ApplyController@confirmRoleApply');
    Route::post('/createRole','RoleController@createRole');
});
Route::post('/testRegister','TestController@register');
