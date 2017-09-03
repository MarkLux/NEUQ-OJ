<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午10:13
 */

Route::group(['middleware' => 'token'],function(){
    Route::post('/problem/create','ProblemController@addProblem');
    Route::post('/problem/{id}/submit','ProblemController@submitProblem');
    Route::post('/problem/{id}/delete','ProblemController@deleteProblem');
    Route::post('/problem/{id}/update','ProblemController@updateProblem');
    Route::get('/problems/mine','ProblemController@getMyProblems');
    Route::get('/problem/{problemId}/rundata','RunDataController@getRunDataList');
    Route::post('/rundata','RunDataController@getRunData');
    Route::post('/problem/{id}/rundata/add','RunDataController@uploadRunData');
    Route::post('/rundata/delete','RunDataController@deleteRunData');
});

Route::group(['middleware' => 'user'],function(){
    Route::get('/problem/{id}','ProblemController@getProblem');
    Route::get('/problems','ProblemController@getProblems');
    Route::get('/problems/search','ProblemController@searchProblems');
});


