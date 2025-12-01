<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Configuration
    |--------------------------------------------------------------------------
    */

    // Length of the CAPTCHA code
    'length' => env('MKY_CAPTCHA_LENGTH', 6),

    // Image dimensions
    'width' => env('MKY_CAPTCHA_WIDTH', 200),
    'height' => env('MKY_CAPTCHA_HEIGHT', 60),

    // Enable or disable audio feature
    'audio_enabled' => env('MKY_CAPTCHA_AUDIO_ENABLED', true),

    // Character set (alphanumeric by default)
    'characters' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',

    // Session key to store captcha
    'session_key' => 'mky_captcha_code',

    // Expiration time in minutes
    'expire' => 5,

    // Image settings
    'background_color' => [255, 255, 255], // RGB
    'text_color' => [0, 0, 0], // RGB
    'line_color' => [100, 100, 100], // RGB for noise lines
    'noise_lines' => 5, // Number of noise lines
    'noise_dots' => 50, // Number of noise dots

    // Font settings
    'font_size' => 20,
    'angle_min' => -15, // Minimum character rotation angle
    'angle_max' => 15, // Maximum character rotation angle

    // Audio path (relative to public folder)
    'audio_path' => 'vendor/mky-captcha/audio',
];
