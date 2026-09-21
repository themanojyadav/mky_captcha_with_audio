<?php

namespace Mky\CaptchaWithAudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Mky\CaptchaWithAudio\CaptchaGenerator;

class CaptchaController extends Controller
{
    protected CaptchaGenerator $captcha;

    public function __construct(CaptchaGenerator $captcha)
    {
        $this->captcha = $captcha;
    }

    /**
     * Generate new CAPTCHA
     */
    public function generate(): JsonResponse
    {
        $data = $this->captcha->refresh();

        return response()->json([
            'success' => true,
            'image' => $data['image'],
            'audio' => $data['audio'],
        ]);
    }

    /**
     * Refresh CAPTCHA
     */
    public function refresh(): JsonResponse
    {
        return $this->generate();
    }

    /**
     * Serve audio file via opaque token (VAPT fix).
     *
     * Reads the small MP3 file directly into memory for fastest possible response.
     * The token is cryptographically random with no relationship to the character.
     */
    public function serveAudio(string $token)
    {
        $tokenMap = Session::get('mky_captcha_audio_tokens', []);

        if (!isset($tokenMap[$token])) {
            abort(404);
        }

        $char = $tokenMap[$token];
        $audioPath = config('mky-captcha.audio_path', 'vendor/mky-captcha/audio');
        $filePath = public_path("{$audioPath}/{$char}.mp3");

        if (!file_exists($filePath)) {
            $filePath = realpath(_DIR_ . '/../../../resources/audio/' . $char . '.mp3');
        }

        if (!$filePath || !file_exists($filePath)) {
            abort(404);
        }

        return response(file_get_contents($filePath), 200, [
            'Content-Type' => 'audio/mpeg',
            'Content-Length' => filesize($filePath),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}