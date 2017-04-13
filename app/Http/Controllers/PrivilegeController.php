<?php

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\PrivilegeService;

class PrivilegeController extends Controller
{
    private $privilegeService;

    public function __construct(PrivilegeService $privilegeService)
    {
        $this->privilegeService = $privilegeService;
    }
}