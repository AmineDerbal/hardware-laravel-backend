<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\UserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::controller(RegisterController::class)->group(function () {
    Route::post('auth/register', 'register');
    Route::get('auth/verify/{token}', 'verifyEmail')->name('email.verify');
});

Route::controller(LoginController::class)->group(function () {
    Route::post('auth/login', 'login');
});

Route::resource('users', \App\Http\Controllers\Api\UserController::class)->except(['update'])->middleware(['auth:api', 'admin.check']);
