<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\UserVoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserVoiceController extends Controller
{
    /**
     * Handle voting on a user voice suggestion.
     *
     * @param Request $request
     * @param UserVoice $userVoice
     * @return JsonResponse
     */
    public function vote(Request $request, UserVoice $userVoice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'in:up,down'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type');

        if ($type === 'up') {
            $userVoice->increment('votes_up');
        } else {
            $userVoice->increment('votes_down');
        }

        return response()->json([
            'data' => [
                'id' => (string)$userVoice->id,
                'type' => 'user-voices',
                'attributes' => [
                    'votes_up' => $userVoice->votes_up,
                    'votes_down' => $userVoice->votes_down,
                ]
            ]
        ]);
    }
}
