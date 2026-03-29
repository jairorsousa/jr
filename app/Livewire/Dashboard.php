<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\CreditCard;
use App\Models\Event;
use App\Models\Task;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function markAsPaid(string $id): void
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
        app(BalanceService::class)->recalculate($transaction->account);
        session()->flash('success', 'Transacao marcada como paga.');
    }

    public function render()
    {
        $now = Carbon::now();

        // Summary cards
        $totalBalance = Account::where('is_active', true)->sum('current_balance');

        $monthlyIncome = Transaction::where('type', TransactionType::Income)
            ->where('is_paid', true)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $monthlyExpense = Transaction::where('type', TransactionType::Expense)
            ->where('is_paid', true)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        // Current invoice of the main (first active) credit card
        $mainCard = CreditCard::where('is_active', true)->first();
        $currentInvoice = 0;
        $invoiceStatus = null;
        if ($mainCard) {
            $invoiceService = app(InvoiceService::class);
            $currentInvoice = $invoiceService->getCurrentInvoiceAmount($mainCard);
            $invoiceMonth = $invoiceService->getInvoiceMonth($mainCard, $now);
            $inv = $mainCard->invoices()
                ->where('reference_month', $invoiceMonth->format('Y-m-d'))
                ->first();
            $invoiceStatus = $inv ? ($inv->is_paid ? 'paga' : ($inv->is_closed ? 'fechada' : 'aberta')) : null;
        }

        // Chart data: last 6 months income vs expense
        $chartData = $this->getChartData();

        // Upcoming bills (next 7 days + overdue)
        $upcomingBills = Transaction::with(['category', 'account'])
            ->where('is_paid', false)
            ->where(function ($q) use ($now) {
                $q->whereBetween('due_date', [$now->copy()->startOfDay(), $now->copy()->addDays(7)->endOfDay()])
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('due_date', '<', $now->startOfDay())
                         ->whereNotNull('due_date');
                  });
            })
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Upcoming events (today + tomorrow)
        $upcomingEvents = Event::where('start_at', '>=', $now->copy()->startOfDay())
            ->where('start_at', '<=', $now->copy()->addDay()->endOfDay())
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        // Top 5 pending tasks by priority
        $pendingTasks = Task::whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
            ->orderByRaw("CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5 END")
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Patrimony evolution (last 12 months)
        $patrimonyData = $this->getPatrimonyData();

        return view('livewire.dashboard', compact(
            'totalBalance', 'monthlyIncome', 'monthlyExpense',
            'mainCard', 'currentInvoice', 'invoiceStatus',
            'chartData', 'upcomingBills', 'upcomingEvents',
            'pendingTasks', 'patrimonyData'
        ));
    }

    private function getChartData(): array
    {
        $months = [];
        $incomes = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = ucfirst($date->translatedFormat('M'));

            $incomes[] = (float) Transaction::where('type', TransactionType::Income)
                ->where('is_paid', true)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');

            $expenses[] = (float) Transaction::where('type', TransactionType::Expense)
                ->where('is_paid', true)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');
        }

        return compact('months', 'incomes', 'expenses');
    }

    private function getPatrimonyData(): array
    {
        $months = [];
        $values = [];
        $currentBalance = (float) Account::where('is_active', true)->sum('current_balance');

        // Work backwards from current balance
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M/y');

            if ($i === 0) {
                $values[] = $currentBalance;
            } else {
                // Approximate: subtract net changes of subsequent months
                $netChange = 0;
                for ($j = 0; $j < $i; $j++) {
                    $d = Carbon::now()->subMonths($j);
                    $income = (float) Transaction::where('type', TransactionType::Income)
                        ->where('is_paid', true)
                        ->whereMonth('date', $d->month)
                        ->whereYear('date', $d->year)
                        ->sum('amount');
                    $expense = (float) Transaction::where('type', TransactionType::Expense)
                        ->where('is_paid', true)
                        ->whereMonth('date', $d->month)
                        ->whereYear('date', $d->year)
                        ->sum('amount');
                    $netChange += ($income - $expense);
                }
                $values[] = $currentBalance - $netChange;
            }
        }

        return compact('months', 'values');
    }
}
