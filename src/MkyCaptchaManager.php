<?php

namespace Mky\CaptchaWithAudio;

class MkyCaptchaManager
{
    public function refresh(?int $length = null): array
    {
        return app(CaptchaGenerator::class)->refresh($length);
    }

    public function render()
    {
        // Return the view that renders the captcha
        // Assuming the user wants to easily display it
        // We can return the view instance
        return view('mky-captcha::captcha');
    }

    /**
     * Proxy other calls to the generator if needed, or expose generator
     */
    public function generator(): CaptchaGenerator
    {
        return app(CaptchaGenerator::class);
    }
}
