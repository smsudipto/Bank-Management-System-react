<?php

namespace App\Providers;

use App\Models\LoginHistory;
use App\Models\SecurityActivityLog;
use App\Models\SecuritySetting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $request = request();
            LoginHistory::create([
                'user_id' => $event->user->getAuthIdentifier(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $this->browser($request->userAgent()),
                'operating_system' => $this->operatingSystem($request->userAgent()),
                'device_type' => $this->deviceType($request->userAgent()),
                'login_status' => 'success',
            ]);
            SecuritySetting::firstOrCreate(['user_id' => $event->user->getAuthIdentifier()])
                ->update(['last_successful_login_at' => now()]);
            SecurityActivityLog::create([
                'user_id' => $event->user->getAuthIdentifier(),
                'activity_type' => 'successful_login',
                'description' => 'A successful login was recorded.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $request = request();
            $userId = $event->user?->getAuthIdentifier();
            if ($userId === null) {
                return;
            }

            LoginHistory::create([
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $this->browser($request->userAgent()),
                'operating_system' => $this->operatingSystem($request->userAgent()),
                'device_type' => $this->deviceType($request->userAgent()),
                'login_status' => 'failed',
            ]);
            SecurityActivityLog::create([
                'user_id' => $userId,
                'activity_type' => 'failed_login',
                'description' => 'A failed login attempt was recorded.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
            ]);
        });
    }

    private function browser(?string $userAgent): string
    {
        return match (true) {
            str_contains((string) $userAgent, 'Edg/') => 'Edge',
            str_contains((string) $userAgent, 'Chrome/') => 'Chrome',
            str_contains((string) $userAgent, 'Firefox/') => 'Firefox',
            str_contains((string) $userAgent, 'Safari/') => 'Safari',
            default => 'Unknown',
        };
    }

    private function operatingSystem(?string $userAgent): string
    {
        return match (true) {
            str_contains((string) $userAgent, 'Windows') => 'Windows',
            str_contains((string) $userAgent, 'Mac OS') => 'macOS',
            str_contains((string) $userAgent, 'Android') => 'Android',
            str_contains((string) $userAgent, 'iPhone') => 'iOS',
            str_contains((string) $userAgent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private function deviceType(?string $userAgent): string
    {
        return preg_match('/Mobile|Android|iPhone/i', (string) $userAgent) === 1 ? 'Mobile' : 'Desktop';
    }
}
