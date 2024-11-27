<?php

use Illuminate\Support\Facades\Route;

Route::any('{any}', function () {
    return response()->json(['message' => 'Forbidden.'], 403);
})->where('any', '.*');