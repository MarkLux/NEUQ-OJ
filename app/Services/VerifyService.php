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
use NEUQOJ\Exceptions\UserGroup\OperationTooQuickException;
use NEUQOJ\Exceptions\UserIsActivatedException;
use NEUQOJ\Exceptions\UserLockedException;
use NEUQOJ\Exceptions\UserNotActivatedException;
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
        //先生成验证码并存在数据库中,其实相当于临时的token
        $code = md5(uniqid());

        $now = Utils::createTimeStamp();

        $verifyCode = [
            'user_id' => $user->id,
            'type' => 1,//1是激活类邮件
            'via' => 1,//发送方式：邮箱
            'code' => $code,
            'created_at' => $now,
            'updated_at' => $now,
            'expires_at' => $now+10800000
        ];

        //先检测verifyCode是否已经存在
        $preVerifyCode = $this->verifyCodeRepo->getByMult(['user_id' => $user->id,'type' => 1,'via' => 1])->first();

        //没有，新建
        if($preVerifyCode == null) {
            if($this->verifyCodeRepo->insert($verifyCode)!=1) return false;
        }
        else
        {
            //判断发送间隔是否太短(60s)
            if($now - $preVerifyCode->updated_at < 60000)
                throw new OperationTooQuickException();

            if($this->verifyCodeRepo->updateWhere(['user_id' => $user->id,'type' => 1,'via' => 1],
                ['code' => $code,'updated_at'=>$now,'expires_at'=>$now+10800000])!=1)
                return false;
        }

        Mail::send('emails.register',['verifyCode' => $code,'name' => $user->name],function ($mail)use($user){
            $mail->from('stump2011@163.com','NEUQ-OJ');
            $mail->to($user->email,$user->name)->subject('注册验证邮件');
        });

        return true;
    }

    public function resendVerifyEmail(User $user): bool
    {
        $preVerifyCode = $this->verifyCodeRepo->getBy('user_id',$user->id)->first();

        if ($preVerifyCode == null) {
            throw new UserNotExistException();
        }

        $now = Utils::createTimeStamp();

        if( ($now - $preVerifyCode->updated_at) < 60000)
            throw new OperationTooQuickException();

        $newCode = md5(uniqid());

        if( $this->verifyCodeRepo->updateWhere(['user_id' => $user->id],
            ['code' => $newCode,'updated_at'=>$now,'expires_at'=>$now+10800000]) != 1) {
            return false;
        }

        Mail::send('emails.register',['verifyCode' => $newCode,'name' => $user->name],function ($mail)use($user){
            $mail->from('stump2011@163.com','NEUQ-OJ');
            $mail->to($user->email,$user->name)->subject('注册验证邮件');
        });

        return true;
    }

    public function activeUserByEmailCode(string $verifyCode): int
    {
        $code = $this->verifyCodeRepo->getBy('code',$verifyCode)->first();

        if ($code == null) {
            throw new UserNotExistException();
        }

        $now = Utils::createTimeStamp();

        //过期
        if($code->expires_at < $now)
            throw new VerifyCodeExpiresException();

        $userId = $code->user_id;
        $flag = -1;

        //验证通过，激活用户,删除验证码条目
        DB::transaction(function()use($userId,&$flag){
            $this->userRepo->updateWhere(['id' => $userId],['status' => 1]);
            $this->verifyCodeRepo->deleteWhere(['user_id'=>$userId,'type'=>1,'via'=>1]);
            $flag = $userId;
        });

        return $flag;
    }


    public function sendResetPasswordEmail(string $email):bool
    {
        $user = $this->userRepo->getBy('email',$email,['id','email','name','status'])->first();

        if ($user == null) {
            throw new UserNotExistException();
        }

        if ($user->status == 0) {
            throw new UserNotActivatedException();
        }else if ($user->status == -1) {
            throw new UserLockedException();
        }

        $preCode = $this->verifyCodeRepo->getByMult(['user_id' => $user->id,'type' => 2,'via' => 1])->first();

        $now = Utils::createTimeStamp();

        $code = md5(uniqid());

        if ($preCode == null) {
            // 生成新的
            $verifyCode = [
                'user_id' => $user->id,
                'type' => 2, // 2是重置密码类
                'via' => 1, // 发送方式：邮箱
                'code' => $code,
                'created_at' => $now,
                'updated_at' => $now,
                'expires_at' => $now + 10800000
            ];

            if ($this->verifyCodeRepo->insert($verifyCode) != 1)
                return false;
        }else{
            if ($now - $preCode->updated_at < 60000)
                throw new OperationTooQuickException();
            if ($this->verifyCodeRepo->updateWhere(['user_id' => $user->id,'type' => 2,'via' => 1],
                    ['code' => $code,'updated_at' => $now,'expires_at' => $now + 1080000]) != 1)
                return false;
        }

        Mail::send('emails.reset-password',['verifyCode' => $code,'name' => $user->name],function ($mail)use($user){
            $mail->from('stump2011@163.com','NEUQ-OJ');
            $mail->to($user->email,$user->name)->subject('重置密码链接');
        });

        return true;
    }

    public function checkUserByVerifyCode(string $verifyCode):int
    {
        $code = $this->verifyCodeRepo->getBy('code',$verifyCode)->first();

        if ($code == null)
            throw new UserNotExistException();
        if ($code->via!=1 || $code->type!= 2)
            return -1;

        $now = Utils::createTimeStamp();

        if ($code->expires_at < $now)
            throw new VerifyCodeExpiresException();

        //验证通过，删除验证码条目
        if($this->verifyCodeRepo->deleteWhere(['user_id'=>$code->user_id,'type'=>2,'via'=>1])!=1)
            return -1;

        return $code->user_id;
    }
}