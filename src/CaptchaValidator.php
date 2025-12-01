<?php

namespace Mky\CaptchaWithAudio;

use Illuminate\Support\Facades\Session;

class CaptchaValidator
{
    /**
     * Validate CAPTCHA code
     */
    public function validate(?string $input): bool
    {
        if (empty($input)) {
            return false;
        }

        $sessionKey = config('mky-captcha.session_key');
        $sessionData = Session::get($sessionKey);

        if (!$sessionData || !isset($sessionData['code'], $sessionData['timestamp'])) {
            return false;
        }

        // Check if CAPTCHA has expired
        $expireMinutes = config('mky-captcha.expire', 5);
        $expireTime = $sessionData['timestamp'] + ($expireMinutes * 60);

        if (time() > $expireTime) {
            Session::forget($sessionKey);
            return false;
        }

        // Case-insensitive comparison
        $isValid = strtoupper($input) === strtoupper($sessionData['code']);

        // Remove CAPTCHA from session after validation attempt (one-time use)
        if ($isValid) {
            Session::forget($sessionKey);
        }

        return $isValid;
    }

    /**
     * Check if CAPTCHA exists in session
     */
    public function exists(): bool
    {
        $sessionData = Session::get(config('mky-captcha.session_key'));
        return !empty($sessionData) && isset($sessionData['code']);
    }

    /**
     * Clear CAPTCHA from session
     */
    public function clear(): void
    {
        Session::forget(config('mky-captcha.session_key'));
    }
}
