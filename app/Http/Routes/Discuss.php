<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/1/7
 * Time: 下午10:50
 */

Route::post('/topic/create','DiscussionController@addTopic');
Route::get('/topic/delete','DiscussionController@deleteTopic');
Route::post('/topic/update','DiscussionController@updateTopic');
Route::post('/topic/search','DiscussionController@searchTopic');

Route::post('/reply/create','DiscussionController@addReply');

Route::get('/topic/stick','DiscussionController@stick');
Route::get('/topic/unstick','DiscussionController@unstick');