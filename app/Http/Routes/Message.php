<?php
Route::post('/message/send','MessageController@sendMessage');
Route::post('/message/getMessages/{id}','MessageController@getUserMessages');
Route::get('/message/checkMessage/{id}','MessageController@checkUserMessage');
Route::post('/message/getUnreadMessages/{id}','MessageController@getUserUnreadMessages');
Route::get('/message/getUnreadMessageCount/{id}','MessageController@getUserUnreadMessageCount');
Route::post('/tag/updateTag','TagsController@updateTag');
Route::post('/tag/updateTag','TagsController@updateTag');