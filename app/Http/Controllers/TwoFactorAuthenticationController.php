<?php

namespace App\Http\Controllers;

use App\Models\RecoveryCode;
use App\Models\SecurityActivityLog;
use App\Models\SecuritySetting;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthenticationController extends Controller
{
    public function start(Request $request, TotpService $totp): RedirectResponse
    {
        $settings = SecuritySetting::firstOrCreate(['user_id' => $request->user()->id]);
        $secret = $totp->secret();
        $settings->update(['two_factor_secret' => $secret, 'two_factor_enabled' => false]);

        $request->session()->put([
            'security_2fa_setup' => true,
            'security_2fa_secret' => $secret,
        ]);

        return back()->with('two_factor_setup', true);
    }

    public function qrCode(Request $request, TotpService $totp): Response
    {
        $secret = $request->session()->get('security_2fa_secret');
        if (! is_string($secret) || $secret === '') {
            abort(404);
        }

        $svg = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
            'outputBase64' => false,
            'scale' => 5,
        ])))->render($totp->provisioningUri($secret, $request->user()->email));

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function verify(Request $request, TotpService $totp): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $settings = SecuritySetting::where('user_id', $request->user()->id)->first();

        if (! $request->session()->get('security_2fa_setup') || ! $settings?->two_factor_secret
            || ! $totp->verify($settings->two_factor_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The authenticator code is invalid or has expired.'], 'two_factor');
        }

        $settings->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
        $request->session()->forget(['security_2fa_setup', 'security_2fa_secret']);
        $codes = $this->replaceRecoveryCodes($request->user()->id);
        $this->log($request, 'two_factor_enabled', 'Two-factor authentication was enabled.');

        return back()->with('success', 'Two-factor authentication is enabled.')
            ->with('recovery_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);
        $settings = SecuritySetting::where('user_id', $request->user()->id)->first();
        if (! $settings?->two_factor_enabled) {
            return back()->withErrors(['current_password' => 'Two-factor authentication is already disabled.'], 'two_factor');
        }

        $settings->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        RecoveryCode::where('user_id', $request->user()->id)->delete();
        $this->log($request, 'two_factor_disabled', 'Two-factor authentication was disabled.');

        return back()->with('success', 'Two-factor authentication is disabled.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $settings = SecuritySetting::where('user_id', $request->user()->id)->first();
        if (! $settings?->two_factor_enabled) {
            return back()->withErrors(['current_password' => 'Enable two-factor authentication first.'], 'two_factor');
        }

        $codes = $this->replaceRecoveryCodes($request->user()->id);
        $this->log($request, 'recovery_codes_regenerated', 'Two-factor recovery codes were regenerated.');

        return back()->with('success', 'New recovery codes were generated.')
            ->with('recovery_codes', $codes);
    }

    public function verifyRecoveryCode(Request $request): RedirectResponse
    {
        $validated = $request->validate(['recovery_code' => ['required', 'string', 'max:20']]);
        $used = DB::transaction(function () use ($request, $validated): bool {
            $codes = RecoveryCode::where('user_id', $request->user()->id)
                ->whereNull('used_at')->lockForUpdate()->get();
            foreach ($codes as $code) {
                if (Hash::check($validated['recovery_code'], $code->code_hash)) {
                    $code->update(['used_at' => now()]);
                    return true;
                }
            }

            return false;
        });

        if (! $used) {
            return back()->withErrors(['recovery_code' => 'That recovery code is invalid or has already been used.'], 'two_factor');
        }

        $this->log($request, 'recovery_code_used', 'A two-factor recovery code was used.');

        return back()->with('success', 'The recovery code was accepted.');
    }

    private function replaceRecoveryCodes(int $userId): array
    {
        RecoveryCode::where('user_id', $userId)->delete();
        $codes = [];
        for ($index = 0; $index < 8; $index++) {
            $code = strtoupper(Str::random(4).'-'.Str::random(4));
            $codes[] = $code;
            RecoveryCode::create(['user_id' => $userId, 'code_hash' => Hash::make($code)]);
        }

        return $codes;
    }

    private function log(Request $request, string $type, string $description): void
    {
        SecurityActivityLog::create([
            'user_id' => $request->user()->id,
            'activity_type' => $type,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
