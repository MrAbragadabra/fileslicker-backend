<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SignupController extends Controller
{
    public function signup(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email|max:255|unique:users",
            "name" => "required|string|max:50",
            "password" => "required|string|min:8"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            "email" => $request->email,
            "name" => $request->name,
            "password" => Hash::make($request->password)
        ]);

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
