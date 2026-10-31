<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FundTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_transfer_debits_sender_and_credits_receiver_atomically(): void
    {
        [$sender, $receiver] = $this->createAccounts('100.00');

        $response = $this->actingAs($sender)->post(route('customer.fund-transfer.store'), $this->transferData($receiver, '40.00'));

        $response->assertRedirect();
        $this->assertDatabaseHas('bank_accounts', ['id' => $sender->bankAccount->id, 'balance' => '60.00']);
        $this->assertDatabaseHas('bank_accounts', ['id' => $receiver->bankAccount->id, 'balance' => '40.00']);
        $this->assertDatabaseCount('transfers', 1);
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_invalid_inactive_and_self_recipients_are_rejected(): void
    {
        [$sender, $receiver] = $this->createAccounts('100.00');

        $this->actingAs($sender)
            ->post(route('customer.fund-transfer.store'), $this->transferData($receiver, '10.00', '4829-invalid'))
            ->assertSessionHasErrors('recipient_account_number');

        $receiver->bankAccount->update(['status' => 'suspended']);
        $this->actingAs($sender)
            ->post(route('customer.fund-transfer.store'), $this->transferData($receiver, '10.00'))
            ->assertSessionHasErrors('recipient_account_number');

        $this->actingAs($sender)
            ->post(route('customer.fund-transfer.store'), $this->transferData($sender, '10.00'))
            ->assertSessionHasErrors('recipient_account_number');
    }

    public function test_amount_and_balance_validation_prevent_transfer(): void
    {
        [$sender, $receiver] = $this->createAccounts('10.00');

        foreach (['0', '-1', '11.00'] as $amount) {
            $this->actingAs($sender)
                ->post(route('customer.fund-transfer.store'), $this->transferData($receiver, $amount))
                ->assertSessionHasErrors('amount');
        }

        $this->assertDatabaseCount('transfers', 0);
    }

    public function test_repeated_idempotency_key_reuses_the_original_transfer(): void
    {
        [$sender, $receiver] = $this->createAccounts('100.00');
        $data = $this->transferData($receiver, '25.00');

        $this->actingAs($sender)->post(route('customer.fund-transfer.store'), $data)->assertRedirect();
        $this->actingAs($sender)->post(route('customer.fund-transfer.store'), $data)->assertRedirect();

        $this->assertDatabaseCount('transfers', 1);
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_sender_and_receiver_can_view_transfer_history_but_other_users_cannot_view_receipt(): void
    {
        [$sender, $receiver] = $this->createAccounts('100.00');
        $outsider = User::factory()->create();
        BankAccount::create([
            'user_id' => $outsider->id,
            'account_number' => '4829-OUTSIDER',
            'account_holder_name' => $outsider->name,
        ]);
        $data = $this->transferData($receiver, '25.00');

        $this->actingAs($sender)->post(route('customer.fund-transfer.store'), $data);
        $transfer = Transfer::firstOrFail();

        $this->actingAs($sender)->get(route('customer.fund-transfer.index'))->assertSee($transfer->transfer_reference);
        $this->actingAs($receiver)->get(route('customer.fund-transfer.index'))->assertSee($transfer->transfer_reference);
        $this->actingAs($outsider)->get(route('customer.fund-transfer.receipt', $transfer))->assertForbidden();
    }

    private function createAccounts(string $senderBalance): array
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        BankAccount::create([
            'user_id' => $sender->id,
            'account_number' => '4829-SENDER01',
            'account_holder_name' => $sender->name,
            'balance' => $senderBalance,
        ]);
        BankAccount::create([
            'user_id' => $receiver->id,
            'account_number' => '4829-RECV0001',
            'account_holder_name' => $receiver->name,
        ]);

        return [$sender->fresh(), $receiver->fresh()];
    }

    private function transferData(User $receiver, string $amount, ?string $accountNumber = null): array
    {
        return [
            'recipient_account_number' => $accountNumber ?? $receiver->bankAccount->account_number,
            'amount' => $amount,
            'description' => 'Test transfer',
            'idempotency_key' => (string) Str::uuid(),
            'current_password' => 'password',
            'confirmed' => '1',
        ];
    }
}
