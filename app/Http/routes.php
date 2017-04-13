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

include 'Routes/Homework.php';

include 'Routes/User.php';

include 'Routes/DeletionLog.php';

include 'Routes/Admin.php';

include 'Routes/Discuss.php';

include 'Routes/Problem.php';

include 'Routes/Status.php';

include 'Routes/Contest.php';

include 'Routes/Index.php';

include 'Routes/Tag.php';

//include 'Routes/Captcha.php';

Route::get('/', function () {
    return 'here is the main page!!';
});

Route::get('/test','TestController@test');

