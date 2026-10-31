<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BankStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_a_statement_with_balances_and_totals(): void
    {
        [$user, $account] = $this->createAccount('115.00');
        $this->transaction($user, $account, 'deposit', '100.00', '2026-10-01');
        $this->transaction($user, $account, 'withdrawal', '25.00', '2026-10-02');
        $this->transaction($user, $account, 'transfer_in', '40.00', '2026-10-03');
        $this->transaction($user, $account, 'transfer_out', '10.00', '2026-10-04');

        $response = $this->actingAs($user)->get(route('customer.bank-statement.index', [
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]));

        $response->assertOk()
            ->assertSee('BDT 100.00')
            ->assertSee('BDT 25.00')
            ->assertSee('BDT 40.00')
            ->assertSee('BDT 10.00')
            ->assertSee('Opening balance');
        $this->assertSame(10.0, (float) $response->viewData('statement')['opening_balance']);
        $this->assertSame(115.0, (float) $response->viewData('statement')['closing_balance']);
    }

    public function test_statement_rejects_invalid_date_ranges_and_requires_authentication(): void
    {
        [$user] = $this->createAccount('0.00');

        $this->get(route('customer.bank-statement.index'))->assertRedirect('/login');
        $this->actingAs($user)
            ->get(route('customer.bank-statement.index', ['from' => '2026-10-31', 'to' => '2026-10-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_statement_excludes_failed_transactions_and_other_users(): void
    {
        [$user, $account] = $this->createAccount('50.00');
        [$otherUser, $otherAccount] = $this->createAccount('75.00', '4829-OTHER001');
        $this->transaction($user, $account, 'deposit', '50.00', '2026-10-05');
        $this->transaction($user, $account, 'deposit', '999.00', '2026-10-06', 'failed');
        $this->transaction($otherUser, $otherAccount, 'deposit', '75.00', '2026-10-06');

        $response = $this->actingAs($user)->get(route('customer.bank-statement.index', [
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]));

        $response->assertOk()->assertDontSee('999.00')->assertDontSee($otherUser->name);
        $this->assertSame(50.0, (float) $response->viewData('statement')['summary']['deposits']);
    }

    public function test_pdf_statement_is_downloadable_for_the_authenticated_account(): void
    {
        [$user] = $this->createAccount('0.00');

        $response = $this->actingAs($user)->get(route('customer.bank-statement.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    private function createAccount(string $balance, string $accountNumber = '4829-STAT0001'): array
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'account_number' => $accountNumber,
            'account_holder_name' => $user->name,
            'balance' => $balance,
        ]);

        return [$user, $account];
    }

    private function transaction(
        User $user,
        BankAccount $account,
        string $type,
        string $amount,
        string $date,
        string $status = 'completed',
    ): void {
        Transaction::create([
            'user_id' => $user->id,
            'bank_account_id' => $account->id,
            'transaction_reference' => (string) Str::uuid(),
            'transaction_type' => $type,
            'amount' => $amount,
            'description' => $type.' test',
            'status' => $status,
            'created_at' => $date.' 12:00:00',
            'updated_at' => $date.' 12:00:00',
        ]);
    }
}
