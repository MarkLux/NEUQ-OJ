<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-2
 * Time: 下午1:41
 */

Route::get('/contests','ContestController@getAllContests');
Route::get('/contest/{id}/ranklist','ContestController@getRankList');
Route::get('/contest/search','ContestController@searchContest');

Route::group(['middleware' => 'user'],function(){
    Route::get('/contest/{id}','ContestController@getContestIndex');
    Route::get('/contest/{cid}/problem/{pnum}','ContestController@getProblem');
    Route::get('/contest/{id}/status','ContestController@getStatus');
});

Route::group(['middleware' => 'token'],function(){
    Route::post('/contest/{id}/problem/{pnum}/submit','ContestController@submitProblem');
    Route::post('/contest/create','ContestController@createContest');
    Route::post('/contest/{id}/join','ContestController@joinContest');
});

