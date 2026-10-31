<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangeVerificationMail;
use App\Models\PasswordChangeVerification;
use App\Models\SecurityActivityLog;
use App\Models\SecuritySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SecurityPasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (Hash::check($validated['password'], $request->user()->password)) {
            return back()->withErrors(['password' => 'The new password must be different from your current password.'], 'password');
        }

        $code = (string) random_int(100000, 999999);
        try {
            Mail::to($request->user()->email)->send(new PasswordChangeVerificationMail($code));
        } catch (TransportExceptionInterface $exception) {
            $this->logMailFailure($request, $exception);
            return back()->withErrors(['password' => 'We could not send the verification email. Please try again later.'], 'password');
        }

        PasswordChangeVerification::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'password_hash' => Hash::make($validated['password']),
                'otp_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(5),
                'last_sent_at' => now(),
                'verified_at' => null,
            ],
        );

        $request->session()->put('password_verification_pending', true);

        return back()
            ->with('success', 'A verification code was sent to your registered email address.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['verification_code' => ['required', 'digits:6']]);
        $verification = PasswordChangeVerification::where('user_id', $request->user()->id)->first();

        if (! $verification || $verification->verified_at || $verification->expires_at->isPast()) {
            return back()->withErrors(['verification_code' => 'The verification code has expired. Request a new code.'], 'password');
        }

        if ($verification->attempts >= 5) {
            return back()->withErrors(['verification_code' => 'Too many incorrect attempts. Request a new code.'], 'password');
        }

        if (! Hash::check($request->string('verification_code')->toString(), $verification->otp_hash)) {
            $verification->increment('attempts');

            return back()->withErrors(['verification_code' => 'The verification code is incorrect.'], 'password');
        }

        $changedAt = now();
        DB::transaction(function () use ($request, $verification, $changedAt): void {
            $request->user()->update(['password' => $verification->password_hash]);
            SecuritySetting::firstOrCreate(['user_id' => $request->user()->id])
                ->update(['last_password_changed_at' => $changedAt]);
            SecurityActivityLog::create([
                'user_id' => $request->user()->id,
                'activity_type' => 'password_changed',
                'description' => 'Your account password was changed.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            $verification->update(['verified_at' => now()]);
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $request->user()->id)
                    ->where('id', '!=', $request->session()->getId())
                    ->delete();
            }
        });

        $request->session()->regenerate();
        $request->session()->forget('password_verification_pending');
        try {
            Mail::to($request->user()->email)->send(new \App\Mail\PasswordChangedMail());
        } catch (TransportExceptionInterface $exception) {
            $this->logMailFailure($request, $exception);
            return back()->with('success', 'Your password was changed successfully on '.$changedAt->format('M j, Y \a\t H:i').'. The confirmation email could not be delivered.');
        }

        return back()->with('success', 'Your password was changed successfully on '.$changedAt->format('M j, Y \a\t H:i').'.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $verification = PasswordChangeVerification::where('user_id', $request->user()->id)->first();
        if (! $verification || $verification->verified_at) {
            return back()->withErrors(['verification_code' => 'Request a password change before resending a code.'], 'password');
        }
        if ($verification->last_sent_at->addSeconds(60)->isFuture()) {
            return back()->withErrors(['verification_code' => 'Please wait before requesting another code.'], 'password');
        }

        $code = (string) random_int(100000, 999999);

        try {
            Mail::to($request->user()->email)->send(new PasswordChangeVerificationMail($code));
        } catch (TransportExceptionInterface $exception) {
            $this->logMailFailure($request, $exception);
            return back()->withErrors(['verification_code' => 'We could not resend the verification email. Please try again later.'], 'password');
        }

        $verification->update([
            'otp_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);
        $request->session()->put('password_verification_pending', true);

        return back()->with('success', 'A new verification code was sent.');
    }

    private function logMailFailure(Request $request, TransportExceptionInterface $exception): void
    {
        Log::warning('Security password OTP email delivery failed.', [
            'user_id' => $request->user()->id,
            'mailer' => config('mail.default'),
            'exception' => $exception::class,
        ]);
    }
}
