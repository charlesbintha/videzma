<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->whereIn('status', ['sent', 'read'])
            ->orderByDesc('sent_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $notifications->map(function ($n) {
                return $this->formatNotification($n);
            }),
        ]);
    }

    /**
     * Compte le nombre de notifications non lues.
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Marque une notification comme lue.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($notification->read_at === null) {
            $notification->update([
                'read_at' => now(),
                'status' => 'read',
            ]);
        }

        return response()->json([
            'data' => $this->formatNotification($notification),
        ]);
    }

    /**
     * Marque toutes les notifications comme lues.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
            ]);

        return response()->json([
            'message' => 'Toutes les notifications ont ete marquees comme lues.',
        ]);
    }

    /**
     * Formate une notification pour la réponse JSON.
     */
    private function formatNotification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'channel' => $notification->channel,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'status' => $notification->status,
            'read' => $notification->read_at !== null,
            'sent_at' => $notification->sent_at?->toIso8601String(),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
