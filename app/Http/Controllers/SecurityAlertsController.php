<?php

namespace App\Http\Controllers;

use App\Models\SecurityActivityLog;
use App\Models\SecuritySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SecurityAlertsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'new_device_login_alert' => ['nullable', 'boolean'],
            'successful_login_alert' => ['nullable', 'boolean'],
            'failed_login_alert' => ['nullable', 'boolean'],
            'suspicious_login_alert' => ['nullable', 'boolean'],
            'password_change_alert' => ['nullable', 'boolean'],
            'two_factor_security_alert' => ['nullable', 'boolean'],
        ]);

        $values = [];
        foreach (array_keys($validated) as $key) {
            $values[$key] = $request->boolean($key);
        }
        SecuritySetting::updateOrCreate(['user_id' => $request->user()->id], $values);
        SecurityActivityLog::create([
            'user_id' => $request->user()->id,
            'activity_type' => 'security_preferences_updated',
            'description' => 'Security alert preferences were updated.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Security alert preferences saved.');
    }
}
