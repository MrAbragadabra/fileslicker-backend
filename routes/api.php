<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/upload-guest', [UploadController::class, 'uploadGuest']);
Route::get('/files/{upload}', [UserController::class, 'getFiles']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/uploads/{user}', [UserController::class, 'getUploads']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/edit', [UserController::class, 'editProfile']);
    Route::post('/upload-user', [UploadController::class, 'uploadUser']);
    Route::post('/upload/delete/{id}', [UploadController::class, 'deleteUpload']);


    Route::middleware('can:is-admin')->group(function () {
        Route::get('/admin/users', [UserController::class, 'listUsers']);
        Route::get('/admin/complaints', [UserController::class, 'listComplaints']);
        Route::get('/admin/files', [UserController::class, 'listFiles']);
        Route::get('/admin/uploads', [UserController::class, 'listUploads']);
        Route::put('/admin/users/{id}/block', [UserController::class, 'blockUser']);
        Route::put('/admin/users/{id}/unblock', [UserController::class, 'unblockUser']);
        Route::put('/admin/complaints/{id}/close', [UserController::class, 'closeComplaint']);
    });
});
