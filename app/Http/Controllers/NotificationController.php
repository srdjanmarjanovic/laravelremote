<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $limit = $request->input('limit', 20);
        $notifications = $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        // If API request, return JSON
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'data' => $notifications,
                'unread_count' => $user->unreadNotifications->count(),
            ]);
        }

        // Mark notifications as read when viewing full page
        $user->unreadNotifications->markAsRead();

        return Inertia::render('Notifications/Index', [
            'notifications' => $user->notifications()->paginate(20),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
