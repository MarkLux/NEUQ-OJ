<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/2/24
 * Time: 下午5:04
 */
namespace NEUQOJ\Services;
/**
 * Laravel 5 Captcha package
 *
 * @copyright Copyright (c) 2015 MeWebStudio
 * @version 2.x
 * @author Muharrem ERİN
 * @contact me@mewebstudio.com
 * @web http://www.mewebstudio.com
 * @date 2015-04-03
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Facades\Redis;
use NEUQOJ\Exceptions\Captcha\CaptchaExpireException;
/**
 * Class Captcha
 * @package Mews\Captcha
 */
class CaptchaService
{
    /**
     * @var Filesystem
     */
    protected $files;
    /**
     * @var Repository
     */
    protected $config;
    /**
     * @var ImageManager
     */
    protected $imageManager;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Hasher
     */
    protected $hasher;
    /**
     * @var Str
     */
    protected $str;
    /**
     * @var ImageManager->canvas
     */
    protected $canvas;
    /**
     * @var ImageManager->image
     */
    protected $image;
    /**
     * @var array
     */
    protected $backgrounds = [];
    /**
     * @var array
     */
    protected $fonts = [];
    /**
     * @var array
     */
    protected $fontColors = [];
    /**
     * @var int
     */
    protected $length = 5;
    /**
     * @var int
     */
    protected $width = 120;
    /**
     * @var int
     */
    protected $height = 36;
    /**
     * @var int
     */
    protected $angle = 15;
    /**
     * @var int
     */
    protected $lines = 3;
    /**
     * @var string
     */
    protected $characters;
    /**
     * @var string
     */
    protected $text;
    /**
     * @var int
     */
    protected $contrast = 0;
    /**
     * @var int
     */
    protected $quality = 90;
    /**
     * @var int
     */
    protected $sharpen = 0;
    /**
     * @var int
     */
    protected $blur = 0;
    //过期时间
    protected $timeout = 600;
    /**
     * @var bool
     */
    protected $bgImage = true;
    /**
     * @var string
     */
    protected $bgColor = '#ffffff';
    /**
     * @var bool
     */
    protected $invert = false;
    /**
     * @var bool
     */
    protected $sensitive = false;
    /**
     * Constructor
     *
     * @param Filesystem $files
     * @param Repository $config
     * @param ImageManager $imageManager
     * @param Session $session
     * @param Hasher $hasher
     * @param Str $str
     * @throws Exception
     * @internal param Validator $validator
     */
    public function __construct(
        Filesystem $files,
        Repository $config,
        ImageManager $imageManager,
        Session $session,
        Hasher $hasher,
        Str $str
    )
    {
        $this->files = $files;
        $this->config = $config;
        $this->imageManager = $imageManager;
        $this->session = $session;
        $this->hasher = $hasher;
        $this->str = $str;
        $this->characters = config('captcha.characters','2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ');
    }
    /**
     * @param string $config
     * @return void
     */
    protected function configure($config)
    {
        if ($this->config->has('captcha.' . $config))
        {
            foreach($this->config->get('captcha.' . $config) as $key => $val)
            {
                $this->{$key} = $val;
            }
        }
    }
    //生成一个验证码会话，返回验证码会话的token
    public function generateCaptcha():string
    {
        //生成验证码会话的token
        $tokenStr = md5(uniqid());
        $key = 'Captcha'.$tokenStr;
        $text = $this->generate();
        //将数据放入缓存中
        Redis::set($key,$text);
        Redis::expire($key,$this->timeout);
        return $tokenStr;
    }
    //刷新一个验证码会话的验证码文字，不存在时抛出异常
    public function refreshCaptcha(string $token)
    {
        $key = 'Captcha'.$token;
        if(!Redis::exists($key))
            throw new CaptchaExpireException();
        $text = $this->generate();
        Redis::set($key,$text);
        Redis::expire($key,$this->timeout);
    }
    //验证指定会话的验证码
    public function checkCaptcha(string $token,string $captchaText):bool
    {
        $key = 'Captcha'.$token;
        if(!Redis::exists($key))
            throw new CaptchaExpireException();
        $text = Redis::get();
        if($text == $captchaText)
            return true;
        return false;
    }
    //获取验证码图片
    public function getCaptcha(string $token,$config = 'default')
    {
        $key = 'Captcha'.$token;
        if(!Redis::exists($key))
            throw new CaptchaExpireException();
        $text = Redis::get($key);
        $this->backgrounds = $this->files->files(__DIR__ . '/../../resources/captcha/assets/backgrounds');
        $this->fonts = $this->files->files(__DIR__ . '/../../resources/captcha/assets/fonts');
        $this->fonts = array_values($this->fonts); //reset fonts array index
        $this->configure($config);
        $this->text = $text;
        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );
        if ($this->bgImage)
        {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        }
        else
        {
            $this->image = $this->canvas;
        }
        if ($this->contrast != 0)
        {
            $this->image->contrast($this->contrast);
        }
        $this->text();
        $this->lines();
        if ($this->sharpen)
        {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert)
        {
            $this->image->invert($this->invert);
        }
        if ($this->blur)
        {
            $this->image->blur($this->blur);
        }
        return $this->image->response('png', $this->quality);
    }
    /**
     * Create captcha image
     *
     * @param string $config
     * @return ImageManager->response
     */
    public function create($config = 'default')
    {
        $this->backgrounds = $this->files->files(__DIR__ . '/../../resources/captcha/assets/backgrounds');
        $this->fonts = $this->files->files(__DIR__ . '/../../resources/captcha/assets/fonts');
        $this->fonts = array_values($this->fonts); //reset fonts array index
        $this->configure($config);
        $this->text = $this->generate();
        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );
        if ($this->bgImage)
        {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        }
        else
        {
            $this->image = $this->canvas;
        }
        if ($this->contrast != 0)
        {
            $this->image->contrast($this->contrast);
        }
        $this->text();
        $this->lines();
        if ($this->sharpen)
        {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert)
        {
            $this->image->invert($this->invert);
        }
        if ($this->blur)
        {
            $this->image->blur($this->blur);
        }
        return $this->image->response('png', $this->quality);
    }
    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background()
    {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }
    /**
     * Generate captcha text
     *
     * @return string
     */
    protected function generate()
    {
        $characters = str_split($this->characters);
        $bag = '';
        for($i = 0; $i < $this->length; $i++)
        {
            $bag .= $characters[rand(0, count($characters) - 1)];
        }
        return $bag;
    }
    /**
     * Writing captcha text
     */
    protected function text()
    {
        $marginTop = $this->image->height() / $this->length;
        $i = 0;
        foreach(str_split($this->text) as $char)
        {
            $marginLeft = ($i * $this->image->width() / $this->length);
            $this->image->text($char, $marginLeft, $marginTop, function($font) {
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });
            $i++;
        }
    }
    /**
     * Image fonts
     *
     * @return string
     */
    protected function font()
    {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }
    /**
     * Random font size
     *
     * @return integer
     */
    protected function fontSize()
    {
        return rand($this->image->height() - 10, $this->image->height());
    }
    /**
     * Random font color
     *
     * @return array
     */
    protected function fontColor()
    {
        if ( ! empty($this->fontColors))
        {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        }
        else
        {
            $color = [rand(0, 255), rand(0, 255), rand(0, 255)];
        }
        return $color;
    }
    /**
     * Angle
     *
     * @return int
     */
    protected function angle()
    {
        return rand((-1 * $this->angle), $this->angle);
    }
    /**
     * Random image lines
     *
     * @return \Intervention\Image\Image
     */
    protected function lines()
    {
        for($i = 0; $i <= $this->lines; $i++)
        {
            $this->image->line(
                rand(0, $this->image->width()) + $i * rand(0, $this->image->height()),
                rand(0, $this->image->height()),
                rand(0, $this->image->width()),
                rand(0, $this->image->height()),
                function ($draw) {
                    $draw->color($this->fontColor());
                }
            );
        }
        return $this->image;
    }
    /**
     * Captcha check
     *
     * @param $value
     * @return bool
     */
    public function check($value)
    {
        if ( ! $this->session->has('captcha'))
        {
            return false;
        }
        $key = $this->session->get('captcha.key');
        if ( ! $this->session->get('captcha.sensitive'))
        {
            $value = $this->str->lower($value);
        }
        $this->session->remove('captcha');
        return $this->hasher->check($value, $key);
    }
    /**
     * Generate captcha image source
     *
     * @param null $config
     * @return string
     */
    public function src($config = null)
    {
        return url('captcha' . ($config ? '/' . $config : '/default')) . '?' . $this->str->random(8);
    }
    /**
     * Generate captcha image html tag
     *
     * @param null $config
     * @return string
     */
    public function img($config = null)
    {
        return '<img src="' . $this->src($config) . '" alt="captcha">';
    }
}