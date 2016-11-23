<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-25
 * Time: 下午8:06
 */

namespace NEUQOJ\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\EmailExistException;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\MobileExistException;
use NEUQOJ\Exceptions\NameExistException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\Contracts\UserServiceInterface;


class UserService implements UserServiceInterface
{
    private $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    public function isUserExist(array $data):bool
    {
        // TODO: Implement isUserExist() method.
        $user = $this->userRepo->getByMult($data)->first();

        if($user == null)
            return false;
        else
            return true;
    }

    public function getUserById(int $userId)
    {
        // TODO: Implement getUserById() method.
        $user = $this->userRepo->get($userId)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;

    }

    public function getUserBy(string $param, $value)
    {
        // TODO: Implement getUserBy() method.
        $user = $this->userRepo->getBy($param,$value)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUserByMult(array $condition)
    {
        // TODO: Implement getUserByMult() method.
        $user = $this->userRepo->getByMult($condition)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUsers(array $data)
    {
        // TODO: Implement getUsers() method.
        $users = $this->userRepo->getByMult($data);

        if($users == null)
            throw new UserNotExistException();
        else
            return $users;
    }

    public function updateUserById(int $userId,array $data):bool
    {
        // TODO: Implement updateUserById() method.
        if($this->userRepo->update($data,$userId))
            return true;
        else
            return false;
    }

    public function updateUser(array $condition, array $data):bool
    {
        // TODO: Implement updateUser() method.
        if($this->userRepo->updateWhere($condition,$data))
            return true;
        else
            return false;
    }

    public function createUser(array $data):bool
    {
        // TODO: Implement createUser() method.
        if($this->userRepo->insert($data))
            return true;
        else
            return false;
    }

    public function lockUser(int $userId):bool
    {
       // TODO: Implement lockUser() method.
        $user = $this->userRepo->get($userId)->first();

        if($user == null)
            throw new UserNotExistException();

        $data = [
            'status' => -1
        ];
        $this->userRepo->update($data,$user['id']);

        return true;
    }

    public function unlockUser(int $userId):bool
    {
        // TODO: Implement unlockUser() method.
        $user = $this->userRepo->get($userId)->first();

        if($user == null)
            throw new UserNotExistException();

        $data = [
            'status' => 0
        ];
        $this->userRepo->update($data,$user['id']);

        return true;
    }

    public function register(array $data):bool
    {
        // TODO: Implement register() method.

        //检查手机号
        if(!Utils::IsMobile($data['mobile']))
            throw new FormValidatorException(['Mobile Number Error']);

        if($this->isUserExist(['mobile' => $data['mobile']]))
            throw new MobileExistException();

        if($this->isUserExist(['email' => $data['email']]))
            throw new EmailExistException();

        if($this->isUserExist(['name'=>$data['name']]))
            throw new NameExistException();


        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'school' => $data['school'] ? $data['school'] : "Unknown",
        ];

        $this->createUser($user);

        return true;
    }

    public function login(array $data)
    {
        // TODO: Implement login() method.
        //正则判断登录名类型
        if(Utils::IsMobile($data['identifier'])) {
            $user = $this->getUserBy('mobile',$data['identifier']);
        } elseif(Utils::IsEmail($data['identifier'])) {
            $user = $this->getUserBy('email',$data['identifier']);
        } else {
            throw new FormValidatorException(["Invalid Identifier Format"]);
        }

        if($user == null)
            throw new UserNotExistException();

        if(!Hash::check($data['password'],$user->password))
            throw new PasswordErrorException();

        return $user;
    }

    public function getUserRole(int $userId)
    {
        // TODO: Implement getUserRole() method.
    }
}