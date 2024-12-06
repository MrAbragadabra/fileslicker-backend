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
Route::post('/complaint/add', [UserController::class, 'complaintAdd']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/uploads/{user}', [UserController::class, 'getUploads']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/edit', [UserController::class, 'editProfile']);
    Route::post('/upload-user', [UploadController::class, 'uploadUser']);
    Route::post('/upload/delete/{id}', [UploadController::class, 'deleteUpload']);
    Route::post('/profile/delete', [UserController::class, 'deleteProfile']);


    Route::middleware('can:is-admin')->group(function () {
        Route::post('/admin/upload/delete/{id}', [UploadController::class, 'deleteUploadAdmin']);
        Route::get('/admin/users', [UserController::class, 'listUsers']);
        Route::get('/admin/complaints', [UserController::class, 'listComplaints']);
        Route::get('/admin/files', [UserController::class, 'listFiles']);
        Route::get('/admin/uploads', [UserController::class, 'listUploads']);
        Route::post('/admin/users/block/{id}', [UserController::class, 'blockUser']);
        Route::post('/admin/users/unblock/{id}', [UserController::class, 'unblockUser']);
        Route::post('/admin/users/grant/{id}', [UserController::class, 'grantAdmin']);
        Route::post('/admin/users/revoke/{id}', [UserController::class, 'revokeAdmin']);
        Route::post('/admin/complaint/close/{id}', [UserController::class, 'closeComplaint']);
    });
});
