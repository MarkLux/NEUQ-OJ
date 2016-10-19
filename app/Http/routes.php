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

Route::get('/a', function (\NEUQOJ\Repository\Eloquent\RoleRepository $repository) {
    return $repository->getByMult(['id' => 1, 'name' => 'ff'],['name']);
    return view('welcome');
});
