<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|min:3|max:30|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'avatar' => 'nullable|string',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'avatar' => $data['avatar'] ?? '🐱',
        ]);

        return $this->respondWithToken($user);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Nepareizs e-pasts vai parole'
            ], 401);
        }

        return $this->respondWithToken($user);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Lietotājs izrakstīts']);
    }

    private function respondWithToken(User $user)
    {
        $token = $user->createToken('frontend')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
