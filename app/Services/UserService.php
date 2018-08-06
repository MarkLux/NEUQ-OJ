<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-25
 * Time: 下午8:06
 */

namespace NEUQOJ\Services;

use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\EmailExistException;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\MobileExistException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserLockedException;
use NEUQOJ\Exceptions\UserNotActivatedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\Contracts\UserServiceInterface;


class UserService implements UserServiceInterface
{
    private $userRepo;
    private $tokenService;
    private $verifyService;

    public function __construct(UserRepository $userRepository, TokenService $tokenService,
                                VerifyService $verifyService)
    {
        $this->userRepo = $userRepository;
        $this->tokenService = $tokenService;
        $this->verifyService = $verifyService;
    }

    public function isUserExist(array $data): bool
    {
        $user = $this->userRepo->getByMult($data)->first();

        if ($user == null)
            return false;
        else
            return true;
    }

    public function getUserById(int $userId, array $columns = ['*'])
    {
        $user = $this->userRepo->get($userId, $columns)->first();
        if ($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUserBy(string $param, $value, array $columns = ['*'])
    {
        $user = $this->userRepo->getBy($param, $value, $columns)->first();
//        if ($user == null)
//            throw new UserNotExistException();
//        else
            return $user;
    }

    public function getUserByMult(array $condition, array $columns = ['*'])
    {
        $user = $this->userRepo->getByMult($condition, $columns)->first();
        if ($user == null)
            throw new UserNotExistException();
        else
            return $user;
    }

    public function getUsers(array $data, array $columns = ['*'])
    {
        $users = $this->userRepo->getByMult($data, $columns);

        if (empty($users))
            throw new UserNotExistException();
        else
            return $users;
    }

    public function updateUserById(int $userId, array $data): bool
    {
        if ($this->userRepo->update($data, $userId)) {
            return true;
        }
        else
            return false;
    }

    public function updateUser(array $condition, array $data): bool
    {
        if ($this->userRepo->updateWhere($condition, $data))
            return true;
        else
            return false;
    }

    public function createUser(array $data): bool
    {
        // TODO 要修改 这个方法主要用于不通过注册来（批量）生成用户
        if ($this->userRepo->insert($data) == 1)
            return true;
        else
            return false;
    }

    public function lockUser(int $userId): bool
    {
        $user = $this->userRepo->get($userId)->first();

        if ($user == null)
            throw new UserNotExistException();

        $data = [
            'status' => -1
        ];
        $this->userRepo->update($data, $user['id']);

        return true;
    }

    public function unlockUser(int $userId): bool
    {
        $user = $this->userRepo->get($userId)->first();

        if ($user == null)
            throw new UserNotExistException();

        $data = [
            'status' => 1
        ];
        $this->userRepo->update($data, $user['id']);

        return true;
    }

    public function register(array $data): int
    {
        //检查手机号
        if (!Utils::IsMobile($data['mobile']))
            throw new FormValidatorException(['Mobile Number Error']);
        //检查邮箱
        if (!Utils::IsEmail($data['email']))
            throw new FormValidatorException(['Email address Error']);
        if (!Utils::isEmailAvailable($data['email']))
            throw new FormValidatorException(['Email address is not Available']);

        if ($this->isUserExist(['mobile' => $data['mobile']]))
            throw new MobileExistException();

        if ($this->isUserExist(['email' => $data['email']]))
            throw new EmailExistException();


        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Utils::pwGen($data['password']),
            'school' => isset($data['school']) ? $data['school'] : "Unknown",
            'status' => 1 // 邮件激活在这里设定
        ];

        $id = $this->userRepo->insertWithId($user);

        return $id;
    }
    public function getUserId($identifier){
        if (Utils::IsMobile($identifier)) {
            $user = $this->getUserBy('mobile', $identifier);
        } elseif (Utils::IsEmail($identifier)) {
            $user = $this->getUserBy('email', $identifier);
        } else {
            //旧用户登录查找
            $user = $this->getUserBy('login_name', $identifier);
        }
        if ($user == null)
            $user = $this->getUserBy('email', $identifier."@acmclub.cn");
        if ($user == null)
            throw new UserNotExistException();
        return $user->id;
    }
    public function login(array $data)
    {
        //正则判断登录名类型
        if (Utils::IsMobile($data['identifier'])) {
            $user = $this->getUserBy('mobile', $data['identifier']);
        } elseif (Utils::IsEmail($data['identifier'])) {
            $user = $this->getUserBy('email', $data['identifier']);
        } else {
            //旧用户登录查找
            $user = $this->getUserBy('login_name', $data['identifier']);
        }
        if ($user == null)
            $user = $this->getUserBy('email', $data['identifier']."@acmclub.cn");
        if ($user == null)
            throw new UserNotExistException();

        if ($user->status == 0)
            throw new UserNotActivatedException();
        elseif ($user->status == -1)
            throw new UserLockedException();

        if (!Utils::pwCheck($data['password'], $user->password))
            throw new PasswordErrorException();
        return $user;
    }

    public function resetPasswordByOldPass(int $userId, string $oldPass, string $newPass): bool
    {
        $user = $this->userRepo->get($userId, ['password'])->first();

        if ($user == null) throw new UserNotExistException();

        if (!Utils::pwCheck($oldPass, $user->password))
            throw new PasswordErrorException();

        $newPass = Utils::pwGen($newPass);

        return $this->userRepo->update(['password' => $newPass], $userId) == 1;
    }

    public function resetPasswordByVerifyCode(string $verifyCode, string $newPass): bool
    {
        $userId = $this->verifyService->checkUserByVerifyCode($verifyCode);

        if ($userId == -1)
            return false;

        if ($this->userRepo->update(['password' => Utils::pwGen($newPass)], $userId) == 1)
            return true;
        else
            return false;
    }

    //非普通登录
    public function loginUser(int $userId, string $ip)
    {
        $user = $this->userRepo->get($userId)->first();
        if ($user == null)
            throw new UserNotExistException();
        $token = $this->tokenService->makeToken($userId, $ip);

        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;

        return $data;
    }

    public function getUserRole(int $userId)
    {
        return Permission::getUserRole($userId);
    }

    public function getRankList(string $scope, int $page, int $size)
    {
        $startDate = '';

        switch ($scope) {
            //按日获取
            case "d":
                $startDate = date('Y') . '-' . date('m') . '-' . date('d');
                break;
            //按周获取
            case "w":
                $monday = mktime(0, 0, 0, date('m'), date('d') - (date('w') + 7) % 8 + 1, date('Y'));
                $startDate = strftime("%Y-%m-%d", $monday);
                break;
            //按月获取
            case "m":
                $startDate = date('Y') . '-' . date('m') . '-01';
                break;
            default:
                $startDate = 'total';
        }

        return $this->userRepo->getRankList($startDate, $page, $size);
    }
}