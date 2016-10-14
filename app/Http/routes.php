<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function (\NEUQOJ\Repository\Eloquent\NewsRepository $repository) {
    return $repository->getByMult(['id' => 1, 'title' => '22'],['content']);
    return view('welcome');
});
