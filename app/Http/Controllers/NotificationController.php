<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // 最新20件の通知を取得
        $notifications = $user->notifications()->take(20)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function read($id)
    {
        $notification = \Illuminate\Support\Facades\Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['status' => 'success']);
    }

    public function readAll()
    {
        \Illuminate\Support\Facades\Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    }
}
