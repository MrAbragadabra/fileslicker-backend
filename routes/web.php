<?php

use Illuminate\Support\Facades\Route;

// Разрешить доступ только к файлам в папке /public/storage/uploads/{any}
Route::get('storage/uploads/{any}', function ($any) {
    $path = public_path('storage/uploads/' . $any); // Путь к файлам в папке uploads

    if (file_exists($path)) {
        return response()->download($path); // Отдаём файл для скачивания
    }

    return response()->json(['message' => 'File not found.'], 404); // Если файл не найден
})->where('any', '.*'); // Разрешаем любую строку после "uploads/"

// Заблокировать все остальные маршруты
Route::any('{any}', function () {
    return response()->json(['message' => 'Forbidden.'], 403);
})->where('any', '.*');
