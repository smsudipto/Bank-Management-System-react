<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_authentication_and_shows_account_information(): void
    {
        [$user, $account] = $this->createCustomer();

        $this->get(route('customer.profile.index'))->assertRedirect('/login');

        $this->actingAs($user)->get(route('customer.profile.index'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('•••• '.substr($account->account_number, -4))
            ->assertSee('BDT 125.00');
    }

    public function test_customer_can_update_personal_information(): void
    {
        [$user] = $this->createCustomer();

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'phone' => '01712345678',
        ])->assertRedirect(route('customer.profile.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'phone' => '01712345678',
        ]);
    }

    public function test_duplicate_email_and_invalid_phone_are_rejected(): void
    {
        [$user] = $this->createCustomer();
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $otherUser->email,
            'phone' => '12345',
        ])->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_customer_can_upload_replace_and_remove_profile_photo(): void
    {
        Storage::fake('local');
        [$user] = $this->createCustomer();

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->createWithContent('first.png', $this->pngContent()),
        ])->assertRedirect();

        $user->refresh();
        $firstPhoto = $user->profile_photo_path;
        Storage::disk('local')->assertExists($firstPhoto);

        $this->actingAs($user)->get(route('customer.profile.photo'))->assertOk();
        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->createWithContent('second.jpg', $this->pngContent()),
        ])->assertRedirect();

        $user->refresh();
        Storage::disk('local')->assertMissing($firstPhoto);
        Storage::disk('local')->assertExists($user->profile_photo_path);

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'remove_profile_photo' => '1',
        ])->assertRedirect();

        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }

    public function test_profile_cannot_change_protected_bank_account_values(): void
    {
        [$user, $account] = $this->createCustomer();

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'account_number' => '9999-TAMPERED',
            'balance' => '999999.00',
            'status' => 'closed',
            'account_type' => 'Business',
        ])->assertRedirect();

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'account_number' => $account->account_number,
            'balance' => '125.00',
            'status' => 'active',
            'account_type' => 'Personal',
        ]);
    }

    public function test_invalid_profile_photo_is_rejected(): void
    {
        [$user] = $this->createCustomer();

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('profile_photo');
    }

    private function createCustomer(): array
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'account_number' => '4829-PROF0001',
            'account_holder_name' => $user->name,
            'balance' => '125.00',
        ]);

        return [$user, $account];
    }

    private function pngContent(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
}
