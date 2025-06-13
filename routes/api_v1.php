<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//The named route is needed by the MustVeryfyEmail trait=>
//$this->notify(new VerifyEmail); ... VerifyEmail references it... sanctum goes south otherwise
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'emailVerification'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'emailVerificationNotify'])
    ->middleware(['auth:sanctum'])->name('verification.send'); //here too

Route::middleware(['auth:sanctum', 'verified'])->group(function ()
{
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    Route::get('/users', [UserController::class, 'show']);

    Route::get('/friends', [FriendController::class, 'show']);
    Route::post('/friends/{friend}', [FriendController::class, 'store']);

    Route::get('/messages/{user}', [MessageController::class, 'show']);
    Route::post('/message', [MessageController::class, 'store']);
});
