<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use NEUQOJ\Repository\Eloquent\TokenRepository;

use NEUQOJ\Http\Requests;

class TestController extends Controller
{
    //
    public function test (TokenRepository $tokenRepository)
    {
        dd('Success');
    }

}
