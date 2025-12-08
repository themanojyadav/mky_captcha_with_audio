<?php

use Illuminate\Support\Facades\Route;
use Mky\CaptchaWithAudio\Http\Controllers\CaptchaController;

Route::middleware('web')
    ->prefix('mky-captcha')
    ->name('mky-captcha.')
    ->group(function () {

        Route::get('/generate', [CaptchaController::class, 'generate'])->name('generate');
        Route::post('/refresh', [CaptchaController::class, 'refresh'])->name('refresh');
    });
