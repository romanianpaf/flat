<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserVoiceItem;
use App\Models\UserVoiceVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserVoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $items = UserVoiceItem::with(['creator:id,name,email'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('votes_count')
            ->orderBy('created_at', 'desc')
            ->get();

        $userVotes = UserVoiceVote::where('user_id', $user->id)
            ->whereIn('item_id', $items->pluck('id'))
            ->pluck('item_id')
            ->toArray();

        return response()->json([
            'items' => $items,
            'user_votes' => $userVotes,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['locatar', 'cex', 'admin', 'tenantadmin', 'sysadmin'])) {
            return response()->json(['message' => 'Nu ai permisiunea de a crea propuneri.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $item = UserVoiceItem::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'open',
        ]);

        return response()->json(['item' => $item], 201);
    }

    public function vote(Request $request, $id)
    {
        $user = $request->user();
        if (in_array($user->role, ['tehnic'])) {
            return response()->json(['message' => 'Rolul tău nu poate vota.'], 403);
        }

        $item = UserVoiceItem::where('tenant_id', $user->tenant_id)->findOrFail($id);
        if ($item->status !== 'open') {
            return response()->json(['message' => 'Votarea este închisă pentru acest item.'], 400);
        }

        $existing = UserVoiceVote::where('item_id', $item->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Ai votat deja acest item.'], 400);
        }

        DB::transaction(function () use ($user, $item) {
            UserVoiceVote::create([
                'tenant_id' => $user->tenant_id,
                'item_id' => $item->id,
                'user_id' => $user->id,
            ]);
            $item->increment('votes_count');
        });

        return response()->json(['message' => 'Vot înregistrat']);
    }

    public function close(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'tenantadmin', 'sysadmin', 'cex'])) {
            return response()->json(['message' => 'Nu ai permisiunea.'], 403);
        }

        $item = UserVoiceItem::where('tenant_id', $user->tenant_id)->findOrFail($id);
        $item->update(['status' => 'closed']);

        return response()->json(['item' => $item]);
    }
}
