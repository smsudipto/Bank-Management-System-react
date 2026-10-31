<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->notifications();
        $filter = $request->string('filter')->toString();
        $search = $request->string('search')->trim()->toString();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif (in_array($filter, ['deposit', 'withdrawal', 'transfer'], true)) {
            $query->where('data', 'like', '%"category":"'.$filter.'"%');
        }

        if ($search !== '') {
            $query->where('data', 'like', '%'.addcslashes($search, '%_').'%');
        }

        $notifications = $query->latest()->paginate(10)->withQueryString();

        return view('customer.notifications.index', [
            'notifications' => $notifications,
            'totalCount' => $user->notifications()->count(),
            'unreadCount' => $user->unreadNotifications()->count(),
            'readCount' => $user->notifications()->whereNotNull('read_at')->count(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $record = $this->ownedNotification($request, $notification);
        $record->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $this->ownedNotification($request, $notification)->delete();

        return back()->with('success', 'Notification deleted.');
    }

    private function ownedNotification(Request $request, string $notification): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($notification)->firstOrFail();
    }
}
