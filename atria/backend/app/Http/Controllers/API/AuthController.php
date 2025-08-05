<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credențialele furnizate sunt incorecte.'],
            ]);
        }

        // Create access token for the user
        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'message' => 'Autentificare reușită',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'Deconectare reușită',
        ]);
    }
}
