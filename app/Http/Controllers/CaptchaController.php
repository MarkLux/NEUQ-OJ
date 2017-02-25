<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/25
 * Time: 下午7:47
 */
namespace NEUQOJ\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Services\CaptchaService;
class CaptchaController extends Controller
{
    private $captchaService;
    public function __construct(CaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }
    //初始化一个验证码会话
    //这样的做法比较危险，如果有人恶意制造大量的请求很有可能把内存挤爆
    //考虑使用流量桶或者cookie标记来缓解
    public function initCaptchaSession()
    {
        $token = $this->captchaService->generateCaptcha();
        return response()->json([
            'code' => 0,
            'captcha_token' => $token,
            'url' => url('/mark/captcha/get').'?token='.$token
        ]);
    }
    public function getCaptcha(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'token' => 'required|string'
        ]);
        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        //第一个生成的验证码作废
        $this->captchaService->refreshCaptcha($request->token);
        $img = $this->captchaService->getCaptcha($request->token);
        return $img;
    }
}