<?php

namespace Tests\Feature;

use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordChangeVerificationMail;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_settings_requires_authentication_and_loads_for_customer(): void
    {
        $user = User::factory()->create();

        $this->get(route('customer.security.index'))->assertRedirect('/login');
        $this->actingAs($user)->get(route('customer.security.index'))
            ->assertOk()
            ->assertSee('Change password')
            ->assertSee('Login alerts');
    }

    public function test_customer_can_change_their_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Current-password-1')]);
        Mail::fake();

        $this->actingAs($user)->post(route('customer.security.password.update'), [
            'current_password' => 'Current-password-1',
            'password' => 'New-password-2',
            'password_confirmation' => 'New-password-2',
        ])->assertSessionHas('password_verification_pending', true);

        Mail::assertSent(PasswordChangeVerificationMail::class, function (PasswordChangeVerificationMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
        $this->assertTrue(Hash::check('Current-password-1', $user->refresh()->password));
        $verification = \App\Models\PasswordChangeVerification::where('user_id', $user->id)->firstOrFail();
        $code = '123456';
        $verification->update(['otp_hash' => Hash::make($code)]);
        $this->actingAs($user)->post(route('customer.security.password.verify'), [
            'verification_code' => $code,
        ])->assertRedirect();

        $this->assertTrue(Hash::check('New-password-2', $user->refresh()->password));
        $this->assertDatabaseHas('security_activity_logs', ['user_id' => $user->id, 'activity_type' => 'password_changed']);
    }

    public function test_resend_sends_to_the_authenticated_users_email_and_keeps_verification_visible(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Current-password-1')]);
        Mail::fake();

        $this->actingAs($user)->post(route('customer.security.password.update'), [
            'current_password' => 'Current-password-1',
            'password' => 'New-password-2',
            'password_confirmation' => 'New-password-2',
        ]);

        $verification = \App\Models\PasswordChangeVerification::where('user_id', $user->id)->firstOrFail();
        $oldHash = $verification->otp_hash;
        $verification->update(['last_sent_at' => now()->subSeconds(61)]);

        $this->actingAs($user)->post(route('customer.security.password.resend'))
            ->assertSessionHas('password_verification_pending', true)
            ->assertSessionHas('success', 'A new verification code was sent.');

        $verification->refresh();
        $this->assertFalse(Hash::check('000000', $verification->otp_hash));
        $this->assertNotSame($oldHash, $verification->otp_hash);
        Mail::assertSent(PasswordChangeVerificationMail::class, 2);

        $this->actingAs($user)->get(route('customer.security.index'))
            ->assertOk()
            ->assertSee('Email verification code')
            ->assertSee('Verify and change password')
            ->assertSee('Resend verification code');
    }

    public function test_resend_cooldown_does_not_replace_the_existing_otp(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Current-password-1')]);
        Mail::fake();

        $this->actingAs($user)->post(route('customer.security.password.update'), [
            'current_password' => 'Current-password-1',
            'password' => 'New-password-2',
            'password_confirmation' => 'New-password-2',
        ]);

        $verification = \App\Models\PasswordChangeVerification::where('user_id', $user->id)->firstOrFail();
        $oldHash = $verification->otp_hash;

        $this->actingAs($user)->post(route('customer.security.password.resend'))
            ->assertSessionHasErrors(['verification_code' => 'Please wait before requesting another code.']);

        $this->assertSame($oldHash, $verification->fresh()->otp_hash);
        Mail::assertSent(PasswordChangeVerificationMail::class, 1);
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Current-password-1')]);

        $this->actingAs($user)->post(route('customer.security.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'New-password-2',
            'password_confirmation' => 'New-password-2',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_alert_preferences_are_isolated_to_the_authenticated_customer(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post(route('customer.security.alerts.update'), [
            'new_device_login_alert' => false,
            'successful_login_alert' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('security_settings', [
            'user_id' => $user->id,
            'new_device_login_alert' => 0,
            'successful_login_alert' => 1,
        ]);
        $this->assertDatabaseMissing('security_settings', ['user_id' => $other->id]);
    }

    public function test_customer_cannot_terminate_another_customers_session(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        config(['session.driver' => 'database']);
        \DB::table('sessions')->insert([
            'id' => 'other-session',
            'user_id' => $other->id,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)->delete(route('customer.security.sessions.destroy', 'other-session'))
            ->assertSessionHasErrors('session');
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
    }
}
