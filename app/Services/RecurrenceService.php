<?php

namespace App\Services;

use App\Enums\RecurrenceType;
use App\Models\Transaction;
use Carbon\Carbon;

class RecurrenceService
{
    public function generateUpcoming(int $daysAhead = 30): int
    {
        $generated = 0;
        $limit = Carbon::now()->addDays($daysAhead);

        $recurringTransactions = Transaction::where('is_recurring', true)
            ->whereNotNull('recurrence_type')
            ->get();

        foreach ($recurringTransactions as $template) {
            $generated += $this->generateForTransaction($template, $limit);
        }

        return $generated;
    }

    private function generateForTransaction(Transaction $template, Carbon $limit): int
    {
        $generated = 0;
        $interval = match ($template->recurrence_type) {
            RecurrenceType::Daily => '1 day',
            RecurrenceType::Weekly => '1 week',
            RecurrenceType::Monthly => '1 month',
            RecurrenceType::Yearly => '1 year',
            default => '1 month',
        };

        $lastGenerated = Transaction::where('account_id', $template->account_id)
            ->where('description', $template->description)
            ->where('is_recurring', false)
            ->orderByDesc('date')
            ->first();

        $nextDate = $lastGenerated
            ? Carbon::parse($lastGenerated->date)->add($interval)
            : Carbon::parse($template->date)->add($interval);

        $recurrenceEnd = $template->recurrence_end
            ? Carbon::parse($template->recurrence_end)->min($limit)
            : $limit;

        while ($nextDate->lte($recurrenceEnd)) {
            // Check if already exists for this date
            $exists = Transaction::where('account_id', $template->account_id)
                ->where('description', $template->description)
                ->where('date', $nextDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                Transaction::create([
                    'account_id' => $template->account_id,
                    'category_id' => $template->category_id,
                    'credit_card_id' => $template->credit_card_id,
                    'type' => $template->type,
                    'description' => $template->description,
                    'amount' => $template->amount,
                    'date' => $nextDate->format('Y-m-d'),
                    'due_date' => $nextDate->format('Y-m-d'),
                    'is_paid' => false,
                    'is_recurring' => false,
                    'notes' => $template->notes,
                    'tags' => $template->tags,
                ]);
                $generated++;
            }

            $nextDate->add($interval);
        }

        return $generated;
    }
}
