<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/2
 * Time: 下午10:13
 */
Route::group(['middleware' => 'token'],function(){
    Route::get('/homework/{id}','HomeworkController@getHomeworkIndex');
    Route::get('/homework/{hid}/problem/{pnum}','HomeworkController@getProblem');
    Route::get('/homework/{id}/status','HomeworkController@getStatus');
    Route::get('/homework/{id}/rank','HomeworkController@getRankList');
    Route::post('/homework/{hid}/problem/{pnum}','HomeworkController@submitProblem');
    Route::post('/homework/{id}/update/info','HomeworkController@updateHomeworkInfo');
    Route::post('/homework/{id}/update/problems','HomeworkController@updateHomeworkProblems');
    Route::post('/homework/{id}/delete','HomeworkController@deleteHomework');
});