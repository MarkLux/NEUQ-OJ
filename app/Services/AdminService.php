<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/25
 * Time: 上午11:13
 */

namespace NEUQOJ\Services;


use NEUQOJ\Common\Utils;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\Contracts\AdminServiceInterface;

class AdminService implements AdminServiceInterface
{
    private $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    public function generateUsersByPrefix(string $prefix, int $num, array $names = [])
    {
        //如果内容names不为空，那它的大小必须和num相等，按照顺序组合成用户

        $users = [];//最终生成的用户信息数组
        $emails = [];

        //生成的随机密码数组
        $passwords = [];

        //生成登录用的邮箱
        for($i = 1;$i <= $num;$i++)
        {
            $email = $prefix.($i<10?'0'.$i:$i).'@neuqoj.com';
            $password = strtoupper(substr(MD5($prefix.($i<10?'0'.$i:$i).rand(0,9999999)),0,10));

            $users[] = [
                'email' => $email,
                'password'=>Utils::pwGen($password),
                'name' => (isset($names[$i-1])?$names[$i-1]:'noname')
            ];
            $emails[] = $email;
            $passwords[] = $password;
        }


        $this->userRepo->deleteWhereIn('email',$emails);//删除掉重复的

        //插入新的
        $this->userRepo->insert($users);

        $newUsers = $this->userRepo->getIn('email',$emails,['id','name','email'])->toArray();

        for($i=0;$i<count($newUsers);$i++)
        {
            $newUsers[$i]['password'] = $passwords[$i];
        }

        return $newUsers;
    }
}