<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-2
 * Time: 下午1:41
 */

Route::group(['middleware' => 'user'],function (){
    Route::get('/contest/{id}','ContestController@getContestIndex');
    Route::get('/contest/{cid}/problem/{pnum}','ContestController@getProblem');
});

