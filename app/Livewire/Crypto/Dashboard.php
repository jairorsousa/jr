<?php

namespace App\Livewire\Crypto;

use App\Enums\CryptoTransactionStatus;
use App\Models\CryptoAccount;
use App\Models\CryptoTransaction;
use App\Services\CryptoBalanceService;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $currentMonth = '';

    public function mount(): void
    {
        $this->currentMonth = $this->currentMonth ?: now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
    }

    public function goToCurrentMonth(): void
    {
        $this->currentMonth = now()->format('Y-m');
    }

    public function render()
    {
        $ref = Carbon::parse($this->currentMonth . '-01');
        $transactions = CryptoTransaction::with(['cryptoAccount.institution', 'asset', 'network'])
            ->where('status', CryptoTransactionStatus::Confirmed)
            ->whereBetween('occurred_at', [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()])
            ->get();

        $inTotal = $transactions->filter(fn (CryptoTransaction $transaction) => $transaction->type->isIn())->sum('amount_brl');
        $outTotal = $transactions->filter(fn (CryptoTransaction $transaction) => $transaction->type->isOut())->sum('amount_brl');
        $totalBalance = app(CryptoBalanceService::class)->totalBalanceBrl();
        $accounts = CryptoAccount::with(['institution', 'betUser'])
            ->where('is_active', true)
            ->orderByDesc('current_balance_brl')
            ->limit(8)
            ->get();
        $institutionBalances = app(CryptoBalanceService::class)->balanceByInstitution();
        $assetBalances = app(CryptoBalanceService::class)->balanceByAsset();
        $recentTransactions = CryptoTransaction::with(['cryptoAccount.institution', 'asset', 'network'])
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');

        return view('livewire.crypto.dashboard', compact(
            'totalBalance',
            'inTotal',
            'outTotal',
            'accounts',
            'institutionBalances',
            'assetBalances',
            'recentTransactions',
            'monthLabel',
            'isCurrentMonth',
        ));
    }
}
