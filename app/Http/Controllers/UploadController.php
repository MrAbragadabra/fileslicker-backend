<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Upload;
use App\Models\File;

class UploadController extends Controller
{
    // Метод для загрузки файлов гостем
    public function uploadGuest(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'files.*' => 'required|file|max:102400', // Файлы обязательны, размер до 100MB
            'storage_period' => 'required|in:1h,12h,24h,72h,168h,336h,720h', // Период хранения файлов
        ]);

        // Определение даты истечения срока хранения файлов в зависимости от выбранного периода
        $expirationDate = match ($request->storage_period) {
            '1h' => now()->addHours(1),
            '12h' => now()->addHours(12),
            '24h' => now()->addHours(24),
            '72h' => now()->addHours(72),
            '168h' => now()->addHours(168),
            '336h' => now()->addHours(336),
            '720h' => now()->addHours(720),
        };

        // Начало транзакции для безопасного выполнения операций
        DB::beginTransaction();

        try {
            // Создание записи о загрузке в базе данных
            $upload = Upload::create([
                'expiration_date' => $expirationDate,
            ]);

            // Путь к папке для сохранения файлов
            $folder = 'uploads/' . $upload->id;

            $filesData = []; // Массив для хранения данных о файлах

            // Обработка каждого файла
            foreach ($request->file('files') as $file) {
                // Получаем оригинальное имя файла
                $originalFileName = $file->getClientOriginalName();

                // Убираем потенциально опасные символы из имени файла
                $safeFileName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $originalFileName);

                // Сохраняем файл в указанную папку с безопасным именем
                $filePath = $file->storeAs($folder, $safeFileName);

                // Проверка на ошибку при сохранении файла
                if (!$filePath) {
                    return response()->json(['message' => 'Ошибка сохранения файла!', 'file' => $filePath], 500);
                }

                // Добавление данных о файле в массив
                $filesData[] = [
                    'upload_id' => $upload->id,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'file_name' => $originalFileName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Вставка данных о файлах в базу данных
            File::insert($filesData);

            // Завершение транзакции
            DB::commit();

            // Ответ с информацией о загрузке
            return response()->json([
                'message' => 'Файлы успешно загружены!',
                'upload_id' => $upload->id,
                'files' => $filesData,
            ], 201);
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            return response()->json(['message' => 'Хоба оба и сломалось!', 'error' => $e->getMessage()], 500);
        }
    }

    // Метод для загрузки файлов пользователем
    public function uploadUser(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'files.*' => 'required|file|max:102400', // Файлы обязательны, размер до 100MB
            'storage_period' => 'required|in:1h,12h,24h,72h,168h,336h,720h', // Период хранения файлов
        ]);

        // Определение даты истечения срока хранения файлов в зависимости от выбранного периода
        $expirationDate = match ($request->storage_period) {
            '1h' => now()->addHours(1),
            '12h' => now()->addHours(12),
            '24h' => now()->addHours(24),
            '72h' => now()->addHours(72),
            '168h' => now()->addHours(168),
            '336h' => now()->addHours(336),
            '720h' => now()->addHours(720),
        };

        // Начало транзакции
        DB::beginTransaction();

        try {
            // Создание записи о загрузке с привязкой к пользователю
            $upload = Upload::create([
                'user_id' => $request->user()->id, // Пользователь, выполняющий загрузку
                'expiration_date' => $expirationDate,
            ]);

            // Путь к папке для сохранения файлов
            $folder = 'uploads/' . $upload->id;

            $filesData = []; // Массив для хранения данных о файлах

            // Обработка каждого файла
            foreach ($request->file('files') as $file) {
                // Получаем оригинальное имя файла
                $originalFileName = $file->getClientOriginalName();

                // Убираем потенциально опасные символы из имени файла
                $safeFileName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $originalFileName);

                // Сохраняем файл в указанную папку с безопасным именем
                $filePath = $file->storeAs($folder, $safeFileName);

                // Проверка на ошибку при сохранении файла
                if (!$filePath) {
                    return response()->json(['message' => 'Ошибка сохранения файла!', 'file' => $filePath], 500);
                }

                // Добавление данных о файле в массив
                $filesData[] = [
                    'upload_id' => $upload->id,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'file_name' => $originalFileName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Вставка данных о файлах в базу данных
            File::insert($filesData);

            // Завершение транзакции
            DB::commit();

            // Ответ с информацией о загрузке
            return response()->json([
                'message' => 'Файлы успешно загружены!',
                'upload_id' => $upload->id,
                'files' => $filesData,
            ], 201);
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            return response()->json(['message' => 'Хоба оба и сломалось!', 'error' => $e->getMessage()], 500);
        }
    }

    // Метод для удаления загрузки пользователем
    public function deleteUpload($id, Request $request)
    {
        // Поиск записи о загрузке по ID
        $upload = Upload::find($id);

        // Проверка на существование загрузки
        if (!$upload) {
            return response()->json(['message' => 'Загрузка не найдена!'], 404);
        }

        // Проверка прав пользователя на удаление загрузки
        if ($upload->user_id !== $request->user()->id) {
            return response()->json(['message' => 'У вас нет прав на удаление этой загрузки.'], 403);
        }

        // Начало транзакции
        DB::beginTransaction();

        try {
            // Путь к папке загрузки
            $folderPath = "uploads/{$upload->id}";

            // Удаляем папку и все файлы в ней
            Storage::deleteDirectory($folderPath);

            // Удаляем запись о загрузке
            $upload->delete();

            // Завершение транзакции
            DB::commit();

            return response()->json(['message' => 'Загрузка и файлы успешно удалены!'], 200);
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            return response()->json(['message' => 'Ошибка удаления!', 'error' => $e->getMessage()], 500);
        }
    }

    // Метод для удаления загрузки администратором
    public function deleteUploadAdmin($id, Request $request)
    {
        // Поиск записи о загрузке по ID
        $upload = Upload::find($id);

        // Проверка на существование загрузки
        if (!$upload) {
            return response()->json(['message' => 'Загрузка не найдена!'], 404);
        }

        // Начало транзакции
        DB::beginTransaction();

        try {
            // Путь к папке загрузки
            $folderPath = "uploads/{$upload->id}";

            // Удаляем папку и все файлы в ней
            Storage::deleteDirectory($folderPath);

            // Удаляем запись о загрузке
            $upload->delete();

            // Завершение транзакции
            DB::commit();

            return response()->json(['message' => 'Загрузка и файлы успешно удалены!'], 200);
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            return response()->json(['message' => 'Ошибка удаления!', 'error' => $e->getMessage()], 500);
        }
    }
}
