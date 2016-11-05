<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-25
 * Time: 下午8:06
 */

namespace NEUQOJ\Services;

use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;
use Predis\Cluster\Distributor\EmptyRingException;


class UserService
{
    private $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    public function isUserExist(string $attribute,string $param):bool
    {
        $user = $this->userRepo
            ->getBy($attribute,$param)
            ->first();

        if($user == null)
            return false;
        else
            return true;
    }

    public function getUser(int $id, string $attribute = "id"):array
    {
        if($attribute == "id")
            return $this->userRepo
                ->get($id)
                ->first();
        else
            return $this->userRepo
                ->getBy($attribute,$id)
                ->first();
    }

    public function updateUser(array $data,int $id, string $attribute = "id"):int
    {
        return $this->userRepo
            ->update($data,$id,$attribute);
    }

    public function createUser(array $data):bool
    {
        return $this->userRepo
            ->insert($data);
    }

    public function lockUser(int $id):bool
    {
        $user = $this->userRepo
            ->get($id)
            ->first();

        if($user==null) {
            return false;
        }else {
            $this->userRepo
                ->update(['status'=>-1],$user['id']);
            return true;
        }
    }

    public function unlockUser(int $id):bool
    {
        $user = $this->userRepo
            ->get($id)
            ->first();

        if($user==null) {
            return false;
        }else {
            $this->userRepo
                ->update(['status'=>1],$user['id']);
            return true;
        }
    }

}