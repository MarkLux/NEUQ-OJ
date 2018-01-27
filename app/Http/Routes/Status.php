<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-22
 * Time: 下午1:12
 */

Route::get('/status','SolutionController@getSolutions');

Route::get('/solution/{id}','SolutionController@getSolution');

Route::get('/status/compile-info/{id}','SolutionController@getCompileInfo');

Route::get('/status/runtime-info/{id}','SolutionController@getRuntimeInfo');

Route::get('/status/source-code/{id}','SolutionController@getSourceCode');

Route::get('/status/statistics','SolutionController@getStatistics');