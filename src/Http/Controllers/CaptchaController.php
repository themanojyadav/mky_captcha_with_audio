<?php

namespace Mky\CaptchaWithAudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
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
}
