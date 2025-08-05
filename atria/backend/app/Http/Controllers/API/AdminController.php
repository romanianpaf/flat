<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function info(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
