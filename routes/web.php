<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\CaptchaController;


Route::get('/', function () {
    return view('userList');
});


Route::get('/get-users', [UserRegisterController::class, 'getUsers']);

Route::get('/register', [UserRegisterController::class, 'showRegisterForm'])->name('register.form');
Route::post('/submitRegister', [UserRegisterController::class, 'register'])->name('register.submit');


Route::get('/generate-captcha', [CaptchaController::class, 'generateCaptcha'])->name('captcha.generate');
Route::post('/verify-captcha', [CaptchaController::class, 'verifyCaptcha'])->name('captcha.verify');



// Route::get('/generate-captcha', [CaptchaController::class, 'generateCaptcha']);
// Route::post('/verify-captcha', [CaptchaController::class, 'verifyCaptcha']);


