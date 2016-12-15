<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午10:13
 */

Route::group(['middleware' => 'token'],function(){
    Route::get('/problem/{id}','ProblemController@getProblem');
    Route::post('/problem/create','ProblemController@addProblem');
});

