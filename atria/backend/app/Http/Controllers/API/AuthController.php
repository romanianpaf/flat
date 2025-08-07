<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Log;
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

        // Log successful login
        Log::logSuccess(
            'user_login',
            "Utilizatorul {$user->name} s-a autentificat cu succes",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'message' => 'Autentificare reușită',
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log logout
        Log::logInfo(
            'user_logout',
            "Utilizatorul {$user->name} s-a deconectat",
            ['user_id' => $user->id]
        );

        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'Deconectare reușită',
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => $user,
        ]);
    }
}
