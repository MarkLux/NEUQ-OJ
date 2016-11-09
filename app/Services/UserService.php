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
use NEUQOJ\Services\Contracts\UserServiceInterface;


class UserService
{
    private $userRepo;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    public function isUserExist(string $attribute,string $param):bool
    {
        $user = $this->userRepo->getBy($attribute,$param)->first();
        if($user == null)
            return false;
        else
            return true;
    }

    public function getUser(int $id,string $attribute = 'id')
    {
        if($attribute == 'id')
            return $this->userRepo->get($id)->first();
        else
            return $this->userRepo->getBy($attribute,$id)->first();
    }

    public function updateUser(array $data,int $id,string $attribute = 'id'):int
    {
        return $this->userRepo->update($data,$id,$attribute);
    }

    public function createUser(array $data):bool
    {
        return $this->userRepo->insert($data);
    }

    public function activeUser($userId)
    {
        $user = $this->userRepo->get($userId);
        if($user!=null && $user->status != 1)
            $this->userRepo->update(['status' => 1],$userId);
    }
}