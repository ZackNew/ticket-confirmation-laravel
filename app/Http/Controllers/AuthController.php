<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($fields);
        $token = $user->createToken($request->name)->plainTextToken;

        return [
            'user' => new AuthResource($user),
            'token' => $token,
        ];
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();
        // Optionally, you can clear all previous tokens for the user if you want to enforce single session login
        // $user->tokens()->delete();
        
        if (!$user || !Hash::check($fields['password'], $user['password'])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken($user->name)->plainTextToken;

        return [
            'user' => new AuthResource($user),
            'token' => $token,
        ];
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
