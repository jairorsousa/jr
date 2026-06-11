<?php

namespace App\Livewire\Bets;

use App\Enums\BetAccountStatus;
use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Models\BetAccount;
use App\Models\BetTransaction;
use App\Services\BetBalanceService;
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
        $from = $ref->copy()->startOfMonth();
        $to = $ref->copy()->endOfMonth();
        $balanceService = app(BetBalanceService::class);

        $periodTotals = $balanceService->periodTotals($from->toDateTimeString(), $to->toDateTimeString());
        $totalBalance = $balanceService->totalBalance();
        $balanceByHouse = $balanceService->balanceByHouse()->take(5);
        $balanceByUser = $balanceService->balanceByUser()->take(5);

        $activeAccounts = BetAccount::where('is_active', true)->count();
        $pendingWithdrawals = BetTransaction::where('type', BetTransactionType::Withdrawal)
            ->where('status', BetTransactionStatus::Pending)
            ->sum('amount');
        $criticalAccounts = BetAccount::with(['bettingHouse', 'betUser'])
            ->whereIn('status', [BetAccountStatus::Limited, BetAccountStatus::Suspended, BetAccountStatus::Blocked])
            ->orderByDesc('current_balance')
            ->take(6)
            ->get();
        $topAccounts = BetAccount::with(['bettingHouse', 'betUser'])
            ->where('is_active', true)
            ->orderByDesc('current_balance')
            ->take(6)
            ->get();
        $latestTransactions = BetTransaction::with(['betAccount.bettingHouse', 'betAccount.betUser'])
            ->orderByDesc('occurred_at')
            ->take(8)
            ->get();

        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');

        return view('livewire.bets.dashboard', compact(
            'periodTotals',
            'totalBalance',
            'balanceByHouse',
            'balanceByUser',
            'activeAccounts',
            'pendingWithdrawals',
            'criticalAccounts',
            'topAccounts',
            'latestTransactions',
            'monthLabel',
            'isCurrentMonth',
        ));
    }
}
