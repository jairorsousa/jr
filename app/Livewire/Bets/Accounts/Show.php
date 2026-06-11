<?php

namespace App\Livewire\Bets\Accounts;

use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Models\BetAccount;
use App\Services\BetBalanceService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public string $id;
    public string $currentMonth = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public string $search = '';

    protected $queryString = [
        'currentMonth' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount(string $id): void
    {
        $this->id = $id;
        $this->currentMonth = $this->currentMonth ?: now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
        $this->resetPage();
    }

    public function goToCurrentMonth(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->resetPage();
    }

    public function recalculate(): void
    {
        app(BetBalanceService::class)->recalculate(BetAccount::findOrFail($this->id));
        session()->flash('success', 'Saldo recalculado com sucesso.');
    }

    public function markChecked(): void
    {
        BetAccount::findOrFail($this->id)->update(['last_checked_at' => now()]);
        session()->flash('success', 'Conferencia registrada.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $account = BetAccount::with(['bettingHouse', 'betUser'])->findOrFail($this->id);
        $ref = Carbon::parse($this->currentMonth . '-01');
        $from = $ref->copy()->startOfMonth();
        $to = $ref->copy()->endOfMonth();

        $query = $account->transactions()
            ->with('financeTransaction.account')
            ->whereBetween('occurred_at', [$from, $to])
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"));

        $confirmed = (clone $query)->where('status', BetTransactionStatus::Confirmed)->get();
        $inTotal = $confirmed->filter(fn ($transaction) => $transaction->type->isIn())->sum('amount');
        $outTotal = $confirmed->filter(fn ($transaction) => $transaction->type->isOut())->sum('amount');
        $stake = $confirmed->filter(fn ($transaction) => $transaction->type === BetTransactionType::BetStake)->sum('amount');
        $payout = $confirmed->filter(fn ($transaction) => $transaction->type === BetTransactionType::BetPayout)->sum('amount');

        $transactions = $query->orderByDesc('occurred_at')->paginate(15);
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');
        $types = BetTransactionType::cases();
        $statuses = BetTransactionStatus::cases();
        $profit = $payout - $stake;
        $roi = $stake > 0 ? ($profit / $stake) * 100 : 0;

        return view('livewire.bets.accounts.show', compact(
            'account',
            'transactions',
            'inTotal',
            'outTotal',
            'stake',
            'payout',
            'profit',
            'roi',
            'monthLabel',
            'isCurrentMonth',
            'types',
            'statuses',
        ));
    }
}
