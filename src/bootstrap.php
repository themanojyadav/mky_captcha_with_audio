<?php

// Manually require package files in dependency order
// This is required for environments where composer dump-autoload cannot be run

require_once __DIR__ . '/CaptchaGenerator.php';
require_once __DIR__ . '/CaptchaValidator.php';
require_once __DIR__ . '/MkyCaptchaManager.php';
require_once __DIR__ . '/Http/Controllers/CaptchaController.php';
require_once __DIR__ . '/Facades/MkyCaptcha.php';
require_once __DIR__ . '/CaptchaServiceProvider.php';

if (function_exists('app')) {
    app()->register(\Mky\CaptchaWithAudio\CaptchaServiceProvider::class);
}
