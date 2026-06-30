<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'read_at' => optional($n->read_at)->format('Y-m-d H:i:s'),
                'created_at' => optional($n->created_at)->format('Y-m-d H:i:s'),
            ]);

        return $this->success([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return $this->success(['message' => 'ok']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(['message' => 'ok']);
    }
}
