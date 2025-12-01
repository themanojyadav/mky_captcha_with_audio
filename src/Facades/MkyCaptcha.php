<?php

namespace Mky\CaptchaWithAudio\Facades;

use Illuminate\Support\Facades\Facade;

class MkyCaptcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mky-captcha';
    }
}
