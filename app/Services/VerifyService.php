<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 上午11:39
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Exceptions\VerifyCodeErrorException;
use NEUQOJ\Exceptions\VerifyCodeExpiresException;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\VerifyCodeRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\Contracts\VerifyServiceInterface;

class VerifyService implements VerifyServiceInterface
{
    private $verifyCodeRepo;
    private $userRepo;

    public function __construct(VerifyCodeRepository $verifyCodeRepository,UserRepository $userRepository)
    {
        $this->verifyCodeRepo = $verifyCodeRepository;
        $this->userRepo = $userRepository;
    }

    public function sendVerifyEmail(User $user): bool
    {
        //先生成验证码并存在数据库中，感觉没必要加密储存
        $code = strtoupper(substr(MD5(rand(0,9999999)),0,6));

        $now = Utils::createTimeStamp();

        $verifyCode = [
            'user_id' => $user->id,
            'type' => 1,//1是激活类邮件
            'via' => 1,//发送方式：邮箱
            'code' => $code,
            'created_at' => $now,
            'updated_at' => $now,
            'expires_at' => $now+3600000
        ];

        //先检测verifyCode是否已经存在
        $preVerifyCode = $this->verifyCodeRepo->getByMult(['user_id' => $user->id,'type' => 1,'via' => 1])->first();

        //没有，新建
        if($preVerifyCode == null)
        {
            if($this->verifyCodeRepo->insert($verifyCode)!=1) return false;
        }
        else
        {
            if($this->verifyCodeRepo->updateWhere(['user_id' => $user->id,'type' => 1,'via' => 1],
                ['code' => $code,'updated_at'=>$now,'expires_at'=>$now+3600000])!=1)
                return false;
        }

        Mail::send('emails.register',['verifyCode' => $code,'name' => $user->name],function ($mail)use($user){
            $mail->from('stump2011@163.com','NEUQ-OJ');
            $mail->to($user->email,$user->name)->subject('注册验证码');
        });

        return true;
    }

    public function activeUserByEmailCode(int $userId, string $verifyCode): bool
    {
        $realCode = $this->verifyCodeRepo->getByMult(['user_id'=>$userId,'type'=>1,'via'=>1])->first();

        if($realCode == null)
            throw new UserNotExistException();

        $now = Utils::createTimeStamp();

        //过期
        if($realCode->expires_at < $now)
            throw new VerifyCodeExpiresException();

        if($realCode->code != $verifyCode)
            throw new VerifyCodeErrorException();

        //验证通过，激活用户,删除验证码条目
        DB::transaction(function()use($userId){
            $this->userRepo->updateWhere(['id' => $userId],['status' => 1]);
            $this->verifyCodeRepo->deleteWhere(['user_id'=>$userId,'type'=>1,'via'=>1]);
        });

        return true;
    }
}