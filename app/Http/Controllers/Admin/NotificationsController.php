<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $notifications = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }
}
