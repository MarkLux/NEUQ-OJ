<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-25
 * Time: 下午8:06
 */

namespace NEUQOJ\Services;

use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;


class UserService
{
    private $userRepo;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    public function isUserExist($attribute,$param)
    {
        $user = $this->userRepo->getBy($attribute,$param)->first();
        if($user == null)
            return false;
        else
            return true;
    }

    public function getUser($id,$attribute = "id")
    {
        if($attribute == "id")
            return $this->userRepo->get($id)->first();
        else
            return $this->userRepo->getBy($attribute,$id)->first();
    }

    public function updateUser(array $data,$id,$attribute = "id")
    {
        return $this->userRepo->update($data,$id,$attribute);
    }

    public function createUser($data)
    {
        return $this->userRepo->insert($data);
    }
}