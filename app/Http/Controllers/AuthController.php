<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Метод для регистрации нового пользователя
    public function register(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:20', // Имя пользователя, строка, максимум 20 символов
            'email' => 'required|string|email|unique:users,email', // Уникальный email, формат email
            'password' => 'required|string|min:8' // Пароль, минимум 8 символов
        ]);

        // Создание нового пользователя в базе данных
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Хэширование пароля
        ]);

        // Ответ с сообщением о успешной регистрации
        return response()->json(['message' => 'Пользователь успешно зареган!'], 201);
    }

    // Метод для аутентификации пользователя (вход)
    public function login(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'email' => 'required|string|email', // Требуется email
            'password' => 'required|string', // Требуется пароль
        ]);

        // Поиск пользователя в базе по email
        $user = User::where('email', $request->email)->first();

        // Проверка существования пользователя и правильности пароля
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Если данные неверны, выбрасывается исключение с сообщением
            throw ValidationException::withMessages([
                'email' => ['Не очень правильные данные вы передали, сударь'],
            ]);
        }

        // Проверка, заблокирован ли пользователь
        if ($user->is_blocked) {
            return response()->json(['message' => 'Пользователь заблокирован'], 403); // Ответ с ошибкой 403 (заблокирован)
        }

        // Создание токена для аутентификации
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ответ с токеном и данными пользователя
        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    // Метод для выхода пользователя (разлогинивания)
    public function logout(Request $request)
    {
        // Удаление всех токенов пользователя, тем самым разлогинивая его
        $request->user()->tokens()->delete();

        // Ответ с сообщением о успешном выходе
        return response()->json(['message' => 'Вышел из конфы'], 200);
    }
}
