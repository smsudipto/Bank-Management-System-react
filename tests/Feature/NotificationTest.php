<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use App\Notifications\DepositCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_requires_authentication_and_shows_counts(): void
    {
        $user = User::factory()->create();
        $this->get(route('customer.notifications.index'))->assertRedirect('/login');

        $user->notify(new DepositCompletedNotification($this->transaction($user)));

        $this->actingAs($user)->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertSee('Deposit completed')
            ->assertSee('Unread');
    }

    public function test_customer_can_filter_mark_read_and_delete_only_their_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify(new DepositCompletedNotification($this->transaction($user)));
        $otherUser->notify(new DepositCompletedNotification($this->transaction($otherUser)));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)->get(route('customer.notifications.index', ['filter' => 'unread']))
            ->assertSee('Deposit completed');
        $this->actingAs($user)->patch(route('customer.notifications.read', $notification->id))
            ->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)->delete(route('customer.notifications.destroy', $otherUser->notifications()->firstOrFail()->id))
            ->assertNotFound();
        $this->actingAs($user)->delete(route('customer.notifications.destroy', $notification->id))
            ->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_mark_all_as_read_updates_only_authenticated_customer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify(new DepositCompletedNotification($this->transaction($user)));
        $otherUser->notify(new DepositCompletedNotification($this->transaction($otherUser)));

        $this->actingAs($user)->post(route('customer.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $otherUser->unreadNotifications()->count());
    }

    public function test_successful_deposit_and_transfer_create_customer_notifications(): void
    {
        [$sender, $receiver] = [$this->createUserWithAccount('100.00'), $this->createUserWithAccount()];

        $this->actingAs($sender['user'])->post(route('deposit.store'), [
            'amount' => '25.00',
            'description' => 'Cash deposit',
        ])->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $sender['user']->id,
            'type' => \App\Notifications\DepositCompletedNotification::class,
        ]);

        $this->actingAs($sender['user'])->post(route('customer.fund-transfer.store'), [
            'recipient_account_number' => $receiver['account']->account_number,
            'amount' => '10.00',
            'description' => 'Notification transfer',
            'idempotency_key' => (string) Str::uuid(),
            'current_password' => 'password',
            'confirmed' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $sender['user']->id,
            'type' => \App\Notifications\TransferSentNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $receiver['user']->id,
            'type' => \App\Notifications\TransferReceivedNotification::class,
        ]);
    }

    private function transaction(User $user): \App\Models\Transaction
    {
        $account = BankAccount::create([
            'user_id' => $user->id,
            'account_number' => '4829-'.strtoupper(str_pad((string) $user->id, 8, '0', STR_PAD_LEFT)),
            'account_holder_name' => $user->name,
            'balance' => '100.00',
        ]);

        return \App\Models\Transaction::create([
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'transaction_reference' => (string) \Illuminate\Support\Str::uuid(),
            'transaction_type' => 'deposit',
            'amount' => '100.00',
            'status' => 'completed',
        ]);
    }

    private function createUserWithAccount(string $balance = '0.00'): array
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'account_number' => '4829-'.strtoupper(str_pad((string) $user->id, 8, '0', STR_PAD_LEFT)),
            'account_holder_name' => $user->name,
            'balance' => $balance,
        ]);

        return compact('user', 'account');
    }
}
