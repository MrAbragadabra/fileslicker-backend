<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('can:is-admin')->group(function () {
        Route::get('/admin/users', [UserController::class, 'listUsers']);
        Route::get('/admin/complaints', [UserController::class, 'listComplaints']);
        Route::get('/admin/files', [UserController::class, 'listFiles']);
        Route::get('/admin/uploads', [UserController::class, 'listUploads']);
    });
});