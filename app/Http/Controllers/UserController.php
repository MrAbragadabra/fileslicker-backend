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

    public function listUsers()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function listComplaints() {
        $complaints = Complaint::all();
        return response()->json($complaints, 200);
    }

    public function listFiles() {
        $files = File::all();
        return response()->json($files, 200);
    }

    public function listUploads() {
        $uploads = Upload::all();
        return response()->json($uploads, 200);
    }
}
