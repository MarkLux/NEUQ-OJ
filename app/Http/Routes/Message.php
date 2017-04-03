<?php
Route::group(['middleware' => 'user'],function(){
    Route::post('/message/send','MessageController@sendMessage');
    Route::get('/message/getMessages','MessageController@getUserMessages');
    Route::get('/message/checkMessage/{MId}','MessageController@checkUserMessage');
    Route::get('/message/getUnreadMessages','MessageController@getUserUnreadMessages');
    Route::get('/message/getUnreadMessageCount','MessageController@getUserUnreadMessageCount');
    Route::get('/message/getMessageCount','MessageController@getUserMessageCount');
    Route::get('/message/deleteOwnMessage','MessageController@deleteOwnMessage');
    Route::post('/message/deleteOwnMessage/{MId}','MessageController@deleteOwnMessage');
});
