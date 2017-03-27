<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午11:15
 */

Route::get('/news/latest','NewsController@getLatestNews');

Route::get('/news','NewsController@getAllNews');

Route::get('/news/{id}','NewsController@getNews');