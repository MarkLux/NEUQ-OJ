<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-15
 * Time: 下午11:59
 */

Route::group(['middleware' => 'user'],function(){
    Route::post('/tag/updateTag','TagsController@updateTag');
    Route::post('/tag/createTags','TagsController@createTags');
    Route::get('/tag/deleteTag','TagsController@deleteTag');
    Route::get('/tag/deleteProblemTag','TagsController@deleteProblemTag');
    Route::post('/tag/updateProblemTag','TagsController@updateProblemTag');
    Route::get('/tag/giveTagTo','TagsController@giveTagTo');
});

Route::get('/tag/getSameTagProblem','TagsController@getSameTagProblem');
Route::get('/tag/getSameSourceProblem','TagsController@getSameSourceProblem');
