<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Upload;

class UserController extends Controller
{
    // Метод для получения профиля текущего пользователя
    public function profile(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    // Метод для редактирования профиля пользователя
    public function editProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20', // Валидация имени пользователя
        ]);

        // Поиск пользователя по ID
        $user = User::findOrFail($request->id);

        // Обновление имени пользователя
        $user->name = $request->name;

        // Сохранение изменений в базе данных
        $user->save();

        return response()->json(['message' => 'Пользователь успешно изменён!'], 200);
    }

    // Метод для получения списка всех пользователей
    public function listUsers()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    // Метод для получения всех жалоб
    public function listComplaints()
    {
        $complaints = Complaint::all();
        return response()->json($complaints, 200);
    }

    // Метод для получения всех файлов
    public function listFiles()
    {
        $files = File::all();
        return response()->json($files, 200);
    }

    // Метод для получения всех загрузок
    public function listUploads()
    {
        $uploads = Upload::all();
        return response()->json($uploads, 200);
    }

    // Метод для получения загрузок конкретного пользователя
    public function getUploads($user)
    {
        $uploads = Upload::where('user_id', $user)->get();
        return response()->json($uploads, 200);
    }

    // Метод для получения файлов, связанных с конкретной загрузкой
    public function getFiles($upload)
    {
        $files = File::where('upload_id', $upload)->get();
        return response()->json($files, 200);
    }

    // Метод для блокировки пользователя
    public function blockUser(Request $request, $id)
    {
        // Поиск пользователя по ID
        $user = User::findOrFail($id);

        // Проверка, заблокирован ли уже пользователь
        if ($user->is_blocked == true) {
            return response()->json(['message' => 'Пользователь уже заблокирован!'], 409);
        }

        // Блокировка пользователя
        $user->is_blocked = true;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно заблокирован!'], 200);
    }

    // Метод для разблокировки пользователя
    public function unblockUser(Request $request, $id)
    {
        // Поиск пользователя по ID
        $user = User::findOrFail($id);

        // Проверка, разблокирован ли уже пользователь
        if ($user->is_blocked == false) {
            return response()->json(['message' => 'Пользователь уже разблокирован!'], 409);
        }

        // Разблокировка пользователя
        $user->is_blocked = false;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно разблокирован!'], 200);
    }

    // Метод для закрытия жалобы
    public function closeComplaint(Request $request, $id)
    {
        // Поиск жалобы по ID
        $compaint = Complaint::findOrFail($id);

        // Проверка, закрыта ли уже жалоба
        if ($compaint->is_close == true) {
            return response()->json(['message' => 'Данная жалоба уже закрыта!'], 409);
        }

        // Закрытие жалобы
        $compaint->is_close = true;
        $compaint->save();

        return response()->json(['message' => 'Жалоба закрыта!'], 200);
    }

    // Метод для удаления профиля пользователя
    public function deleteProfile(Request $request)
    {
        // Получаем текущего пользователя
        $user = $request->user();

        // Проверка на существование пользователя
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден!'], 404);
        }

        // Начало транзакции
        DB::beginTransaction();

        try {
            // Завершаем текущий токен пользователя
            $request->user()->currentAccessToken()->delete();

            // Получаем все загрузки пользователя
            $uploads = Upload::where('user_id', $user->id)->get();

            // Удаляем файлы, связанные с загрузками пользователя
            foreach ($uploads as $upload) {
                $folderPath = "uploads/{$upload->id}";
                Storage::deleteDirectory($folderPath);
            }

            // Удаляем пользователя (каскадно удаляются загрузки и файлы в базе)
            $user->delete();

            DB::commit();

            return response()->json(['message' => 'Профиль пользователя успешно удалён!'], 200);
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            return response()->json(['message' => 'Ошибка удаления профиля!', 'error' => $e->getMessage()], 500);
        }
    }

    // Метод для добавления жалобы
    public function complaintAdd(Request $request)
    {
        // Валидация данных жалобы
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        // Создание записи о жалобе в базе данных
        $complaint = Complaint::create([
            'upload_id' => $request->upload_id,
            'comment' => $request->comment
        ]);

        return response()->json(['message' => 'Жалоба успешно добавлена!'], 201);
    }

    // Метод для предоставления прав администратора пользователю
    public function grantAdmin(Request $request, $id)
    {
        // Поиск пользователя по ID
        $user = User::findOrFail($id);

        // Проверка, является ли пользователь уже администратором
        if ($user->is_admin == true) {
            return response()->json(['message' => 'Пользователь уже админ!'], 409);
        }

        // Предоставление прав администратора
        $user->is_admin = true;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно администрирован!'], 200);
    }

    // Метод для отмены прав администратора у пользователя
    public function revokeAdmin(Request $request, $id)
    {
        // Поиск пользователя по ID
        $user = User::findOrFail($id);

        // Проверка, является ли пользователь администратором
        if ($user->is_admin == false) {
            return response()->json(['message' => 'Пользователь уже не админ!'], 409);
        }

        // Отмена прав администратора
        $user->is_admin = false;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно не администрирован!'], 200);
    }
}
