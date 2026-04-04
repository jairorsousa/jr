<?php

namespace App\Livewire\Financeiro;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;

class Comparacao extends Component
{
    // ── Card A filters ──────────────────────────────────────────────
    public string $aMonth = '';
    public bool $aCustomRange = false;
    public string $aDateFrom = '';
    public string $aDateTo = '';
    public string $aAccount = '';
    public string $aCategory = '';
    public string $aType = '';

    // ── Card B filters ──────────────────────────────────────────────
    public string $bMonth = '';
    public bool $bCustomRange = false;
    public string $bDateFrom = '';
    public string $bDateTo = '';
    public string $bAccount = '';
    public string $bCategory = '';
    public string $bType = '';

    // ── View options ────────────────────────────────────────────────
    public bool $showTransactions = false;
    public string $visibleTransactions = ''; // 'a' or 'b'

    public function mount(): void
    {
        // Card A = mes anterior, Card B = mes atual
        $this->aMonth = now()->subMonth()->format('Y-m');
        $this->bMonth = now()->format('Y-m');
    }

    // ── Card A navigation ───────────────────────────────────────────

    public function aPreviousMonth(): void
    {
        $this->aMonth = Carbon::parse($this->aMonth . '-01')->subMonth()->format('Y-m');
        $this->aCustomRange = false;
        $this->aDateFrom = '';
        $this->aDateTo = '';
    }

    public function aNextMonth(): void
    {
        $this->aMonth = Carbon::parse($this->aMonth . '-01')->addMonth()->format('Y-m');
        $this->aCustomRange = false;
        $this->aDateFrom = '';
        $this->aDateTo = '';
    }

    public function aApplyRange(): void
    {
        if ($this->aDateFrom && $this->aDateTo) {
            $this->aCustomRange = true;
        }
    }

    public function aClearRange(): void
    {
        $this->aCustomRange = false;
        $this->aDateFrom = '';
        $this->aDateTo = '';
    }

    // ── Card B navigation ───────────────────────────────────────────

    public function bPreviousMonth(): void
    {
        $this->bMonth = Carbon::parse($this->bMonth . '-01')->subMonth()->format('Y-m');
        $this->bCustomRange = false;
        $this->bDateFrom = '';
        $this->bDateTo = '';
    }

    public function bNextMonth(): void
    {
        $this->bMonth = Carbon::parse($this->bMonth . '-01')->addMonth()->format('Y-m');
        $this->bCustomRange = false;
        $this->bDateFrom = '';
        $this->bDateTo = '';
    }

    public function bApplyRange(): void
    {
        if ($this->bDateFrom && $this->bDateTo) {
            $this->bCustomRange = true;
        }
    }

    public function bClearRange(): void
    {
        $this->bCustomRange = false;
        $this->bDateFrom = '';
        $this->bDateTo = '';
    }

    // ── Quick presets ───────────────────────────────────────────────

    public function presetSameMonthLastYear(): void
    {
        $this->bMonth = now()->format('Y-m');
        $this->aMonth = now()->subYear()->format('Y-m');
        $this->clearAllRanges();
    }

    public function presetLastTwoMonths(): void
    {
        $this->bMonth = now()->format('Y-m');
        $this->aMonth = now()->subMonth()->format('Y-m');
        $this->clearAllRanges();
    }

    public function presetQuarterVsQuarter(): void
    {
        $now = now();
        $currentQuarterStart = $now->copy()->firstOfQuarter();
        $currentQuarterEnd = $now->copy()->lastOfQuarter();
        $prevQuarterStart = $currentQuarterStart->copy()->subMonths(3);
        $prevQuarterEnd = $currentQuarterStart->copy()->subDay();

        $this->aDateFrom = $prevQuarterStart->format('Y-m-d');
        $this->aDateTo = $prevQuarterEnd->format('Y-m-d');
        $this->aCustomRange = true;

        $this->bDateFrom = $currentQuarterStart->format('Y-m-d');
        $this->bDateTo = $currentQuarterEnd->format('Y-m-d');
        $this->bCustomRange = true;
    }

    public function presetYearVsYear(): void
    {
        $year = now()->year;
        $this->aDateFrom = ($year - 1) . '-01-01';
        $this->aDateTo = ($year - 1) . '-12-31';
        $this->aCustomRange = true;

        $this->bDateFrom = $year . '-01-01';
        $this->bDateTo = now()->format('Y-m-d');
        $this->bCustomRange = true;
    }

    public function copySyncFilters(string $direction): void
    {
        if ($direction === 'a_to_b') {
            $this->bAccount = $this->aAccount;
            $this->bCategory = $this->aCategory;
            $this->bType = $this->aType;
        } else {
            $this->aAccount = $this->bAccount;
            $this->aCategory = $this->bCategory;
            $this->aType = $this->bType;
        }
    }

    public function toggleTransactions(string $card): void
    {
        if ($this->showTransactions && $this->visibleTransactions === $card) {
            $this->showTransactions = false;
            $this->visibleTransactions = '';
        } else {
            $this->showTransactions = true;
            $this->visibleTransactions = $card;
        }
    }

    private function clearAllRanges(): void
    {
        $this->aCustomRange = false;
        $this->aDateFrom = '';
        $this->aDateTo = '';
        $this->bCustomRange = false;
        $this->bDateFrom = '';
        $this->bDateTo = '';
    }

    // ── Query builders ──────────────────────────────────────────────

    private function buildQuery(
        string $month, bool $customRange, string $dateFrom, string $dateTo,
        string $account, string $category, string $type
    ) {
        $query = Transaction::with(['account', 'category']);

        if ($customRange && $dateFrom && $dateTo) {
            $query->where('date', '>=', $dateFrom)->where('date', '<=', $dateTo);
        } else {
            $ref = Carbon::parse($month . '-01');
            $query->where('date', '>=', $ref->startOfMonth()->format('Y-m-d'))
                  ->where('date', '<=', $ref->endOfMonth()->format('Y-m-d'));
        }

        $query->when($account, fn($q) => $q->where('account_id', $account))
              ->when($category, fn($q) => $q->where('category_id', $category))
              ->when($type, fn($q) => $q->where('type', $type));

        return $query;
    }

    private function calculateStats($query): array
    {
        $income = (clone $query)->where('type', TransactionType::Income)->sum('amount');
        $expense = (clone $query)->where('type', TransactionType::Expense)->sum('amount');
        $count = (clone $query)->count();

        // Top categories breakdown
        $topCategories = (clone $query)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(fn($row) => [
                'name' => $row->category?->name ?? 'Sem categoria',
                'color' => $row->category?->color ?? '#94a3b8',
                'total' => (float) $row->total,
            ]);

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) ($income - $expense),
            'count' => $count,
            'topCategories' => $topCategories,
        ];
    }

    private function getLabel(string $month, bool $customRange, string $dateFrom, string $dateTo): string
    {
        if ($customRange && $dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . Carbon::parse($dateTo)->format('d/m/Y');
        }
        $ref = Carbon::parse($month . '-01');
        return ucfirst($ref->translatedFormat('F Y'));
    }

    public function render()
    {
        $queryA = $this->buildQuery($this->aMonth, $this->aCustomRange, $this->aDateFrom, $this->aDateTo, $this->aAccount, $this->aCategory, $this->aType);
        $queryB = $this->buildQuery($this->bMonth, $this->bCustomRange, $this->bDateFrom, $this->bDateTo, $this->bAccount, $this->bCategory, $this->bType);

        $statsA = $this->calculateStats($queryA);
        $statsB = $this->calculateStats($queryB);

        $labelA = $this->getLabel($this->aMonth, $this->aCustomRange, $this->aDateFrom, $this->aDateTo);
        $labelB = $this->getLabel($this->bMonth, $this->bCustomRange, $this->bDateFrom, $this->bDateTo);

        // Differences
        $diff = [
            'income' => $statsB['income'] - $statsA['income'],
            'expense' => $statsB['expense'] - $statsA['expense'],
            'balance' => $statsB['balance'] - $statsA['balance'],
            'income_pct' => $statsA['income'] > 0 ? round(($statsB['income'] - $statsA['income']) / $statsA['income'] * 100, 1) : null,
            'expense_pct' => $statsA['expense'] > 0 ? round(($statsB['expense'] - $statsA['expense']) / $statsA['expense'] * 100, 1) : null,
            'balance_pct' => $statsA['balance'] != 0 ? round(($statsB['balance'] - $statsA['balance']) / abs($statsA['balance']) * 100, 1) : null,
        ];

        // Transactions list (if toggled)
        $transactionsA = collect();
        $transactionsB = collect();
        if ($this->showTransactions) {
            if ($this->visibleTransactions === 'a') {
                $transactionsA = (clone $queryA)->orderByDesc('date')->orderByDesc('created_at')->limit(50)->get();
            } else {
                $transactionsB = (clone $queryB)->orderByDesc('date')->orderByDesc('created_at')->limit(50)->get();
            }
        }

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('livewire.financeiro.comparacao', compact(
            'statsA', 'statsB', 'labelA', 'labelB', 'diff',
            'accounts', 'categories',
            'transactionsA', 'transactionsB',
        ));
    }
}
