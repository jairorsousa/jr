<?php

namespace Tests\Feature\Bets;

use App\Enums\AccountType;
use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BetAccount;
use App\Models\BetUser;
use App\Models\BettingHouse;
use App\Models\Transaction;
use App\Services\BetBalanceService;
use App\Services\BetTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculates_balance_using_only_confirmed_transactions(): void
    {
        $betAccount = $this->createBetAccount(initialBalance: 1000);

        $betAccount->transactions()->create([
            'type' => BetTransactionType::Deposit,
            'status' => BetTransactionStatus::Confirmed,
            'amount' => 500,
            'occurred_at' => '2026-06-01 10:00:00',
            'confirmed_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito inicial',
        ]);

        $betAccount->transactions()->create([
            'type' => BetTransactionType::BetStake,
            'status' => BetTransactionStatus::Confirmed,
            'amount' => 100,
            'occurred_at' => '2026-06-02 10:00:00',
            'confirmed_at' => '2026-06-02 10:00:00',
            'description' => 'Aposta Flamengo',
        ]);

        $betAccount->transactions()->create([
            'type' => BetTransactionType::Withdrawal,
            'status' => BetTransactionStatus::Pending,
            'amount' => 200,
            'occurred_at' => '2026-06-03 10:00:00',
            'description' => 'Saque pendente',
        ]);

        app(BetBalanceService::class)->recalculate($betAccount);

        $this->assertSame('1400.00', $betAccount->fresh()->current_balance);
    }

    public function test_confirmed_deposit_creates_finance_expense_and_updates_balances(): void
    {
        $financeAccount = $this->createFinanceAccount(initialBalance: 1000);
        $betAccount = $this->createBetAccount(initialBalance: 0);

        app(BetTransactionService::class)->create([
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'amount' => 250,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito Betano',
        ], $financeAccount->id);

        $this->assertSame('250.00', $betAccount->fresh()->current_balance);
        $this->assertSame('750.00', $financeAccount->fresh()->current_balance);
        $this->assertDatabaseHas('transactions', [
            'account_id' => $financeAccount->id,
            'type' => TransactionType::Expense->value,
            'amount' => 250,
            'is_paid' => true,
        ]);
    }

    public function test_pending_withdrawal_changes_balance_only_after_confirmation(): void
    {
        $financeAccount = $this->createFinanceAccount(initialBalance: 0);
        $betAccount = $this->createBetAccount(initialBalance: 500);

        $withdrawal = app(BetTransactionService::class)->create([
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Withdrawal->value,
            'status' => BetTransactionStatus::Pending->value,
            'amount' => 200,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Saque Betano',
        ]);

        $this->assertSame('500.00', $betAccount->fresh()->current_balance);
        $this->assertSame(0, Transaction::count());

        app(BetTransactionService::class)->confirm($withdrawal, $financeAccount->id);

        $this->assertSame('300.00', $betAccount->fresh()->current_balance);
        $this->assertSame('200.00', $financeAccount->fresh()->current_balance);
        $this->assertDatabaseHas('transactions', [
            'account_id' => $financeAccount->id,
            'type' => TransactionType::Income->value,
            'amount' => 200,
            'is_paid' => true,
        ]);
    }

    public function test_update_can_unlink_finance_transaction_when_checkbox_is_unchecked(): void
    {
        $financeAccount = $this->createFinanceAccount(initialBalance: 1000);
        $betAccount = $this->createBetAccount(initialBalance: 0);

        $betTransaction = app(BetTransactionService::class)->create([
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'amount' => 250,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito Betano',
        ], $financeAccount->id);

        $this->assertNotNull($betTransaction->fresh()->finance_transaction_id);
        $this->assertSame('750.00', $financeAccount->fresh()->current_balance);
        $this->assertSame(1, Transaction::count());

        app(BetTransactionService::class)->update($betTransaction->fresh(), [
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'amount' => 250,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito Betano',
        ], null, false);

        $this->assertNull($betTransaction->fresh()->finance_transaction_id);
        $this->assertSame('1000.00', $financeAccount->fresh()->current_balance);
        $this->assertSame(0, Transaction::count());
        $this->assertSame('250.00', $betAccount->fresh()->current_balance);
    }

    private function createBetAccount(float $initialBalance): BetAccount
    {
        $house = BettingHouse::create([
            'name' => 'Betano',
            'slug' => 'betano',
            'color' => '#ff6f00',
        ]);

        $betUser = BetUser::create([
            'name' => 'Jairo Rodrigues',
            'color' => '#ff6f00',
        ]);

        return BetAccount::create([
            'betting_house_id' => $house->id,
            'bet_user_id' => $betUser->id,
            'name' => 'Jairo - Betano',
            'status' => 'active',
            'verification_status' => 'verified',
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
        ]);
    }

    private function createFinanceAccount(float $initialBalance): Account
    {
        return Account::create([
            'name' => 'Nubank',
            'type' => AccountType::Checking,
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'color' => '#ff6f00',
            'is_active' => true,
        ]);
    }
}
