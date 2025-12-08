<?php

namespace Mky\CaptchaWithAudio;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Session;

class CaptchaGenerator
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Generate CAPTCHA code
     */
    public function generate(?int $length = null): string
    {
        $length = $length ?? config('mky-captcha.length', 6);
        $characters = config('mky-captcha.characters', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $code = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $charactersLength - 1)];
        }

        // Store in session with timestamp
        Session::put(config('mky-captcha.session_key'), [
            'code' => $code,
            'timestamp' => time(),
        ]);

        return $code;
    }

    /**
     * Create CAPTCHA image
     */
    public function createImage(?string $code = null, ?int $width = null, ?int $height = null): string
    {
        $code = $code ?? $this->generate();
        $width = $width ?? config('mky-captcha.width', 200);
        $height = $height ?? config('mky-captcha.height', 60);

        // Create image
        $image = $this->manager->create($width, $height);

        // Background color
        $bgColor = config('mky-captcha.background_color', [255, 255, 255]);
        $image->fill(sprintf('rgb(%d, %d, %d)', $bgColor[0], $bgColor[1], $bgColor[2]));

        // Add noise lines
        $this->addNoiseLines($image, $width, $height);

        // Add noise dots
        $this->addNoiseDots($image, $width, $height);

        // Add text
        $this->addText($image, $code, $width, $height);

        // Encode to base64
        $encoded = $image->toPng();
        return 'data:image/png;base64,' . base64_encode($encoded);
    }

    /**
     * Add noise lines to image
     */
    protected function addNoiseLines($image, int $width, int $height): void
    {
        $lineColor = config('mky-captcha.line_color', [100, 100, 100]);
        $noiseLines = config('mky-captcha.noise_lines', 5);
        $color = sprintf('rgb(%d, %d, %d)', $lineColor[0], $lineColor[1], $lineColor[2]);

        for ($i = 0; $i < $noiseLines; $i++) {
            $image->drawLine(function ($line) use ($width, $height, $color) {
                $line->from(random_int(0, $width), random_int(0, $height));
                $line->to(random_int(0, $width), random_int(0, $height));
                $line->color($color);
                $line->width(1);
            });
        }
    }

    /**
     * Add noise dots to image
     */
    protected function addNoiseDots($image, int $width, int $height): void
    {
        $noiseDots = config('mky-captcha.noise_dots', 50);

        for ($i = 0; $i < $noiseDots; $i++) {
            $x = random_int(0, $width);
            $y = random_int(0, $height);
            $color = sprintf('rgb(%d, %d, %d)', random_int(0, 255), random_int(0, 255), random_int(0, 255));

            $image->drawPixel($x, $y, $color);
        }
    }

    /**
     * Add text to image
     */
    protected function addText($image, string $code, int $width, int $height): void
    {
        $textColor = config('mky-captcha.text_color', [0, 0, 0]);
        $fontSize  = config('mky-captcha.font_size', 40); // ✅ Larger & clean
        $angleMin = config('mky-captcha.angle_min', -15);
        $angleMax = config('mky-captcha.angle_max', 15);

        $color = sprintf('rgb(%d, %d, %d)', $textColor[0], $textColor[1], $textColor[2]);

        // ✅ Correct absolute font path
        $fontPath = config('mky-captcha.font_path');

        $codeLength = strlen($code);
        $spacing = $width / ($codeLength + 1);

        for ($i = 0; $i < $codeLength; $i++) {
            $x = (int)(($i + 1) * $spacing);
            $y = (int)($height * 0.7) + random_int(-5, 5);
            $angle = random_int($angleMin, $angleMax);

            $image->text($code[$i], $x, $y, function ($font) use ($fontPath, $fontSize, $color, $angle) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color($color);
                $font->align('center');
                $font->valign('middle');
                $font->angle($angle);
            });
        }
    }


    /**
     * Get audio files for the current CAPTCHA
     */
    public function getAudioFiles(): ?array
    {
        if (!config('mky-captcha.audio_enabled', true)) {
            return null;
        }

        $sessionData = Session::get(config('mky-captcha.session_key'));

        if (!$sessionData || !isset($sessionData['code'])) {
            return null;
        }

        $code = $sessionData['code'];
        $audioPath = config('mky-captcha.audio_path', 'vendor/mky-captcha/audio');
        $audioFiles = [];

        for ($i = 0; $i < strlen($code); $i++) {
            $char = strtolower($code[$i]);
            $audioFiles[] = asset("{$audioPath}/{$char}.mp3");
        }

        return $audioFiles;
    }

    /**
     * Refresh CAPTCHA
     */
    public function refresh(?int $length = null): array
    {
        $code = $this->generate($length);

        return [
            'image' => $this->createImage($code),
            'audio' => $this->getAudioFiles(),
        ];
    }
}
