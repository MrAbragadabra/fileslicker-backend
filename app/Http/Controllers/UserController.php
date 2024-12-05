<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Complaint;
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

    public function getFiles($upload) {
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
}
