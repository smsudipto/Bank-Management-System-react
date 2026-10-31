<?php

namespace App\Http\Controllers;

use App\Models\SecuritySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecuritySettingsController extends Controller
{
    public function index(Request $request): View
    {
        $settings = SecuritySetting::firstOrCreate(['user_id' => $request->user()->id]);
        $loginHistory = $request->user()->loginHistories()
            ->when($request->filled('status'), fn ($query) => $query->where('login_status', $request->string('status')->toString()))
            ->latest()->paginate(5, ['*'], 'login_page')->withQueryString();
        $activityLog = $request->user()->securityActivityLogs()
            ->when($request->filled('activity_type'), fn ($query) => $query->where('activity_type', $request->string('activity_type')->toString()))
            ->latest()->paginate(5, ['*'], 'activity_page')->withQueryString();
        $sessions = collect();
        if (config('session.driver') === 'database') {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->id)
                ->latest('last_activity')
                ->get();
        }

        return view('customer.security.index', compact('settings', 'loginHistory', 'activityLog', 'sessions'));
    }
}
