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
    public function profile(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function editProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
        ]);

        $user = User::findOrFail($request->id);

        $user->name = $request->name;

        $user->save();

        return response()->json(['message' => 'Пользователь успешно изменён!'], 200);
    }

    public function listUsers()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function listComplaints()
    {
        $complaints = Complaint::all();
        return response()->json($complaints, 200);
    }

    public function listFiles()
    {
        $files = File::all();
        return response()->json($files, 200);
    }

    public function listUploads()
    {
        $uploads = Upload::all();
        return response()->json($uploads, 200);
    }

    public function getUploads($user)
    {
        $uploads = Upload::where('user_id', $user)->get();
        return response()->json($uploads, 200);
    }

    public function getFiles($upload)
    {
        $files = File::where('upload_id', $upload)->get();
        return response()->json($files, 200);
    }

    public function blockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_blocked == true) {
            return response()->json(['message' => 'Пользователь уже заблокирован!'], 409);
        }

        $user->is_blocked = true;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно заблокирован!'], 200);
    }

    public function unblockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_blocked == false) {
            return response()->json(['message' => 'Пользователь уже разблокирован!'], 409);
        }

        $user->is_blocked = false;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно разблокирован!'], 200);
    }

    public function closeComplaint(Request $request, $id)
    {
        $compaint = Complaint::findOrFail($id);

        if ($compaint->is_close == true) {
            return response()->json(['message' => 'Данная жалоба уже закрыта!'], 409);
        }

        $compaint->is_close == true;
        $compaint->save();

        return response()->json(['message' => 'Жалоба закрыта!'], 200);
    }

    public function deleteProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден!'], 404);
        }

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
            DB::rollBack();
            return response()->json(['message' => 'Ошибка удаления профиля!', 'error' => $e->getMessage()], 500);
        }
    }

    public function complaintAdd(Request $request)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $complaint = Complaint::create([
            'upload_id' => $request->upload_id,
            'comment' => $request->comment
        ]);

        return response()->json(['message' => 'Жалоба успешно добавлена!'], 201);
    }

    public function grantAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_admin == true) {
            return response()->json(['message' => 'Пользователь уже админ!'], 409);
        }

        $user->is_admin = true;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно администрирован!'], 200);
    }

    public function revokeAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_admin == false) {
            return response()->json(['message' => 'Пользователь уже не админ!'], 409);
        }

        $user->is_admin = false;
        $user->save();

        return response()->json(['message' => 'Пользователь успешно не администрирован!'], 200);
    }
}
