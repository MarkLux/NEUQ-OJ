<?php

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-28
 * Time: 下午4:44
 */

Route::get('/deletion-log','Admin\DeletionLogController@getLog');

Route::get('/deletion-log/{id}/confirm','Admin\DeletionLogController@confirmDeletion');

Route::get('/deletion-log/{id}/undo','Admin\DeletionLogController@undoDeletion');
