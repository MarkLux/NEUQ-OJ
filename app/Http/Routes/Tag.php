<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-15
 * Time: 下午11:59
 */

Route::post('/tag/updateTag','TagsController@updateTag');
Route::post('/tag/createTag','TagsController@createTag');
Route::post('/tag/deleteTag','TagsController@deleteTag');
Route::post('/tag/createProblemTag','TagsController@createProblemTag');
Route::post('/tag/deleteProblemTag','TagsController@deleteProblemTag');
Route::post('/tag/updateProblemTag','TagsController@updateProblemTag');
Route::post('/tag/giveTagTo','TagsController@giveTagTo');
Route::get('/tag/getSameTagProblem','TagsController@getSameTagProblem');
Route::get('/tag/getSameSourceProblem','TagsController@getSameSourceProblem');
