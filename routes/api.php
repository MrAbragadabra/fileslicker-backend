<?php

use App\Http\Controllers\SignupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/users/signup', [SignupController::class,'signup']);
