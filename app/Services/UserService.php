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
use NEUQOJ\Services\TokenService;


class UserService implements UserServiceInterface
{
    private $userRepo;
    private $tokenService;

    public function __construct(UserRepository $userRepository,TokenService $tokenService)
    {
        $this->userRepo = $userRepository;
        $this->tokenService = $tokenService;
    }

    public function isUserExist(array $data):bool
    {
        $user = $this->userRepo->getByMult($data)->first();

        if($user == null)
            return false;
        else
            return true;
    }

    public function getUserById(int $userId)
    {
        $user = $this->userRepo->get($userId)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;

    }

    public function getUserBy(string $param, $value)
    {
        $user = $this->userRepo->getBy($param,$value)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUserByMult(array $condition)
    {
        $user = $this->userRepo->getByMult($condition)->first();
        if($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUsers(array $data)
    {
        $users = $this->userRepo->getByMult($data);

        if($users == null)
            throw new UserNotExistException();
        else
            return $users;
    }

    public function updateUserById(int $userId,array $data):bool
    {
        if($this->userRepo->update($data,$userId))
            return true;
        else
            return false;
    }

    public function updateUser(array $condition, array $data):bool
    {
        if($this->userRepo->updateWhere($condition,$data))
            return true;
        else
            return false;
    }

    public function createUser(array $data):bool
    {
        // TODO 要修改 这个方法主要用于不通过注册来（批量）生成用户
        if($this->userRepo->insert($data)==1)
            return true;
        else
            return false;
    }

    public function lockUser(int $userId):bool
    {
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
        $user = $this->userRepo->get($userId)->first();

        if($user == null)
            throw new UserNotExistException();

        $data = [
            'status' => 0
        ];
        $this->userRepo->update($data,$user['id']);

        return true;
    }

    public function register(array $data):int
    {
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

        $id = $this->userRepo->insertWithId($user);

        return $id;
    }

    public function login(array $data)
    {
        //正则判断登录名类型
        if(Utils::IsMobile($data['identifier'])) {
            $user = $this->getUserBy('mobile',$data['identifier']);
        } elseif(Utils::IsEmail($data['identifier'])) {
            $user = $this->getUserBy('email',$data['identifier']);
        } else {
            //添加用户名登陆方式
            $user = $this->getUserBy('name',$data['identifier']);
        }

        if($user == null)
            throw new UserNotExistException();

        if(!Hash::check($data['password'],$user->password))
            throw new PasswordErrorException();

        return $user;
    }

    public function loginUser(int $userId,string $ip)
    {
        $user = $this->userRepo->get($userId)->first();
        if($user==null)
            throw new UserNotExistException();
        $token = $this->tokenService->makeToken($userId,$ip);

        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;

        return $data;
    }

    public function getUserRole(int $userId)
    {
        // TODO: Implement getUserRole() method.
    }
}