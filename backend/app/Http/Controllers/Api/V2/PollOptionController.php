<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\PollOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollOptionController extends Controller
{
    /**
     * Handle voting on a poll option.
     *
     * @param Request $request
     * @param PollOption $pollOption
     * @return JsonResponse
     */
    public function vote(Request $request, PollOption $pollOption): JsonResponse
    {
        // Incrementează numărul de voturi
        $pollOption->increment('votes_count');

        return response()->json([
            'data' => [
                'id' => (string)$pollOption->id,
                'type' => 'poll-options',
                'attributes' => [
                    'option_text' => $pollOption->option_text,
                    'votes_count' => $pollOption->votes_count,
                ]
            ]
        ]);
    }
}

