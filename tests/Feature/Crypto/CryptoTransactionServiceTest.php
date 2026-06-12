<?php

namespace Tests\Feature\Crypto;

use App\Enums\AccountType;
use App\Enums\BetSettlementMethod;
use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Enums\CryptoTransactionStatus;
use App\Enums\CryptoTransactionType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BetAccount;
use App\Models\BetUser;
use App\Models\BettingHouse;
use App\Models\CryptoAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoInstitution;
use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use App\Models\Transaction;
use App\Services\BetTransactionService;
use App\Services\CryptoTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_deposit_creates_finance_expense_and_updates_crypto_balance(): void
    {
        $financeAccount = $this->createFinanceAccount(initialBalance: 1000);
        $cryptoAccount = $this->createCryptoAccount(initialBalance: 0);

        app(CryptoTransactionService::class)->create([
            'crypto_account_id' => $cryptoAccount->id,
            'type' => CryptoTransactionType::BankDeposit->value,
            'status' => CryptoTransactionStatus::Confirmed->value,
            'amount_brl' => 300,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Aporte Binance',
        ], $financeAccount->id);

        $this->assertSame('300.00', $cryptoAccount->fresh()->current_balance_brl);
        $this->assertSame('700.00', $financeAccount->fresh()->current_balance);
        $this->assertDatabaseHas('transactions', [
            'account_id' => $financeAccount->id,
            'type' => TransactionType::Expense->value,
            'amount' => 300,
            'is_paid' => true,
        ]);
    }

    public function test_send_to_bet_decreases_crypto_balance_without_finance_transaction(): void
    {
        $cryptoAccount = $this->createCryptoAccount(initialBalance: 500);

        app(CryptoTransactionService::class)->create([
            'crypto_account_id' => $cryptoAccount->id,
            'type' => CryptoTransactionType::SendToBet->value,
            'status' => CryptoTransactionStatus::Confirmed->value,
            'amount_brl' => 150,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Envio para bet',
        ]);

        $this->assertSame('350.00', $cryptoAccount->fresh()->current_balance_brl);
        $this->assertSame(0, Transaction::count());
    }

    public function test_bet_deposit_can_be_settled_with_crypto_and_then_unlinked(): void
    {
        [$asset, $network] = $this->createAssetAndNetwork();
        $cryptoAccount = $this->createCryptoAccount(initialBalance: 1000);
        $betAccount = $this->createBetAccount(initialBalance: 0);

        $betTransaction = app(BetTransactionService::class)->create([
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'settlement_method' => BetSettlementMethod::Crypto->value,
            'amount' => 200,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito cripto Betano',
        ], null, [
            'crypto_account_id' => $cryptoAccount->id,
            'crypto_asset_id' => $asset->id,
            'crypto_network_id' => $network->id,
            'crypto_amount' => 40,
            'exchange_rate_brl' => 5,
        ]);

        $this->assertSame('200.00', $betAccount->fresh()->current_balance);
        $this->assertSame('800.00', $cryptoAccount->fresh()->current_balance_brl);
        $this->assertNotNull($betTransaction->fresh()->crypto_transaction_id);
        $this->assertNull($betTransaction->fresh()->finance_transaction_id);
        $this->assertSame(1, CryptoTransaction::count());

        app(BetTransactionService::class)->update($betTransaction->fresh(), [
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'settlement_method' => BetSettlementMethod::Manual->value,
            'amount' => 200,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito cripto Betano',
        ], null, false);

        $this->assertNull($betTransaction->fresh()->crypto_transaction_id);
        $this->assertSame(BetSettlementMethod::Manual, $betTransaction->fresh()->settlement_method);
        $this->assertSame(0, CryptoTransaction::count());
        $this->assertSame('1000.00', $cryptoAccount->fresh()->current_balance_brl);
        $this->assertSame('200.00', $betAccount->fresh()->current_balance);
    }

    public function test_switching_bet_deposit_from_bank_to_crypto_removes_finance_transaction(): void
    {
        [$asset, $network] = $this->createAssetAndNetwork();
        $financeAccount = $this->createFinanceAccount(initialBalance: 1000);
        $cryptoAccount = $this->createCryptoAccount(initialBalance: 1000);
        $betAccount = $this->createBetAccount(initialBalance: 0);

        $betTransaction = app(BetTransactionService::class)->create([
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'settlement_method' => BetSettlementMethod::Bank->value,
            'amount' => 250,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito banco Betano',
        ], $financeAccount->id);

        $this->assertNotNull($betTransaction->fresh()->finance_transaction_id);
        $this->assertSame('750.00', $financeAccount->fresh()->current_balance);
        $this->assertSame(1, Transaction::count());

        app(BetTransactionService::class)->update($betTransaction->fresh(), [
            'bet_account_id' => $betAccount->id,
            'type' => BetTransactionType::Deposit->value,
            'status' => BetTransactionStatus::Confirmed->value,
            'settlement_method' => BetSettlementMethod::Crypto->value,
            'amount' => 250,
            'occurred_at' => '2026-06-01 10:00:00',
            'description' => 'Deposito cripto Betano',
        ], null, false, [
            'crypto_account_id' => $cryptoAccount->id,
            'crypto_asset_id' => $asset->id,
            'crypto_network_id' => $network->id,
            'crypto_amount' => 50,
            'exchange_rate_brl' => 5,
        ]);

        $this->assertNull($betTransaction->fresh()->finance_transaction_id);
        $this->assertNotNull($betTransaction->fresh()->crypto_transaction_id);
        $this->assertSame('1000.00', $financeAccount->fresh()->current_balance);
        $this->assertSame('750.00', $cryptoAccount->fresh()->current_balance_brl);
        $this->assertSame(0, Transaction::count());
        $this->assertSame(1, CryptoTransaction::count());
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

    private function createCryptoAccount(float $initialBalance): CryptoAccount
    {
        $institution = CryptoInstitution::create([
            'name' => 'Binance',
            'slug' => 'binance',
            'type' => 'exchange',
            'color' => '#F0B90B',
        ]);

        return CryptoAccount::create([
            'crypto_institution_id' => $institution->id,
            'name' => 'Binance Jairo',
            'custody_type' => 'exchange',
            'initial_balance_brl' => $initialBalance,
            'current_balance_brl' => $initialBalance,
            'is_active' => true,
        ]);
    }

    private function createAssetAndNetwork(): array
    {
        $asset = CryptoAsset::create([
            'symbol' => 'USDT',
            'name' => 'Tether USD',
            'decimals' => 6,
            'is_stablecoin' => true,
            'is_active' => true,
        ]);

        $network = CryptoNetwork::create([
            'native_asset_id' => $asset->id,
            'name' => 'TRON TRC20',
            'code' => 'TRC20',
            'is_active' => true,
        ]);

        return [$asset, $network];
    }
}
