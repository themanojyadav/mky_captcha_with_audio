<?php

namespace Mky\CaptchaWithAudio;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Mky\CaptchaWithAudio\CaptchaValidator as CaptchaValidatorClass;

class CaptchaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind to the Manager class
        $this->app->singleton('mky-captcha', function ($app) {
            return new MkyCaptchaManager();
        });

        // Merge config using absolute path
        $this->mergeConfigFrom(
            __DIR__ . '/config/mky-captcha.php',
            'mky-captcha'
        );
    }

    public function boot(): void
    {
        // Publish config (keep for Composer users)
        $this->publishes([
            __DIR__ . '/config/mky-captcha.php' => config_path('mky-captcha.php'),
        ], 'mky-captcha-config');

        // Publish audio files
        $this->publishes([
            __DIR__ . '/../resources/audio' => public_path('vendor/mky-captcha/audio'),
        ], 'mky-captcha-audio');

        // Publish CSS
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/mky-captcha/css'),
        ], 'mky-captcha-css');

        // Publish JavaScript
        $this->publishes([
            __DIR__ . '/../resources/js' => public_path('vendor/mky-captcha/js'),
        ], 'mky-captcha-js');

        // Publish font
        $this->publishes([
            __DIR__ . '/../resources/fonts' => public_path('vendor/mky-captcha/fonts'),
        ], 'mky-captcha-fonts');

        // Publish assets (CSS + JS)
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/mky-captcha/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/mky-captcha/js'),
        ], 'mky-captcha-assets');

        // Load routes using absolute path
        // Only load if file exists to be safe
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        // Load views using absolute path
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mky-captcha');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mky-captcha'),
        ], 'mky-captcha-views');

        // Register custom validation rule
        Validator::extend('mky_captcha', function ($attribute, $value, $parameters, $validator) {
            return app(CaptchaValidatorClass::class)->validate($value);
        });

        Validator::replacer('mky_captcha', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute is invalid.');
        });
    }
}
