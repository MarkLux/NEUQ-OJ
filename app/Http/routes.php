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


include 'Routes/UserGroup.php';

include 'Routes/Auth.php';

include 'Routes/Test.php';

Route::get('/', function () {
    return 'here is the main page!!';
});

