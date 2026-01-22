<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display list of notifications
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->orderBy('created_at', 'desc');
        
        // Filter by read status
        if ($request->has('unread')) {
            $query->where('is_read', false);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate(20);
        
        // For AJAX requests, return JSON
        if ($request->ajax()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'hasMore' => $notifications->hasMorePages(),
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure notification belongs to user
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        // Redirect to action URL if available
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
