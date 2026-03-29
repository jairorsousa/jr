<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function getOrCreateInvoice(CreditCard $card, Carbon $referenceMonth): CreditCardInvoice
    {
        $ref = $referenceMonth->copy()->startOfMonth();

        $invoice = CreditCardInvoice::where('credit_card_id', $card->id)
            ->where('reference_month', $ref->format('Y-m-d'))
            ->first();

        if ($invoice) {
            return $invoice;
        }

        // Calculate due date: due_day of the month after the reference month
        $dueDate = $ref->copy()->addMonth()->day(min($card->due_day, $ref->copy()->addMonth()->daysInMonth));

        return CreditCardInvoice::create([
            'credit_card_id' => $card->id,
            'reference_month' => $ref->format('Y-m-d'),
            'total_amount' => 0,
            'due_date' => $dueDate->format('Y-m-d'),
            'is_paid' => false,
            'is_closed' => false,
        ]);
    }

    public function calculateTotal(CreditCardInvoice $invoice): float
    {
        $total = $invoice->transactions()->sum('amount');
        $invoice->update(['total_amount' => $total]);
        return (float) $total;
    }

    public function closeInvoice(CreditCardInvoice $invoice): void
    {
        $this->calculateTotal($invoice);
        $invoice->update(['is_closed' => true]);
    }

    public function reopenInvoice(CreditCardInvoice $invoice): void
    {
        if (!$invoice->is_paid) {
            $invoice->update(['is_closed' => false]);
        }
    }

    public function payInvoice(CreditCardInvoice $invoice): void
    {
        $card = $invoice->creditCard;

        if (!$card->account_id) {
            throw new \RuntimeException('Cartao nao possui conta de pagamento vinculada.');
        }

        DB::transaction(function () use ($invoice, $card) {
            $this->calculateTotal($invoice);

            // Create payment transaction on the linked account
            Transaction::create([
                'account_id' => $card->account_id,
                'category_id' => $this->getOrCreateCardCategory()->id,
                'credit_card_id' => $card->id,
                'type' => TransactionType::Expense,
                'description' => "Pagamento fatura {$card->name} - " . Carbon::parse($invoice->reference_month)->format('m/Y'),
                'amount' => $invoice->total_amount,
                'date' => now()->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'is_paid' => true,
                'paid_at' => now(),
            ]);

            $invoice->update([
                'is_paid' => true,
                'is_closed' => true,
                'paid_at' => now(),
            ]);

            // Recalculate linked account balance
            app(BalanceService::class)->recalculate($card->account);
        });
    }

    public function createInstallments(
        CreditCard $card,
        string $description,
        float $totalAmount,
        int $installments,
        Carbon $firstDate,
        ?string $categoryId,
    ): void {
        $installmentAmount = round($totalAmount / $installments, 2);
        $remainder = round($totalAmount - ($installmentAmount * $installments), 2);

        DB::transaction(function () use ($card, $description, $installmentAmount, $remainder, $installments, $firstDate, $categoryId) {
            for ($i = 1; $i <= $installments; $i++) {
                $date = $firstDate->copy()->addMonths($i - 1);
                $amount = $installmentAmount;

                // Add remainder to the last installment
                if ($i === $installments && $remainder !== 0.0) {
                    $amount += $remainder;
                }

                // Determine which invoice this installment belongs to
                $invoiceMonth = $this->getInvoiceMonth($card, $date);
                $invoice = $this->getOrCreateInvoice($card, $invoiceMonth);

                Transaction::create([
                    'account_id' => $card->account_id ?? $this->getDefaultAccountId(),
                    'category_id' => $categoryId,
                    'credit_card_id' => $card->id,
                    'credit_card_invoice_id' => $invoice->id,
                    'type' => TransactionType::Expense,
                    'description' => "{$description} ({$i}/{$installments})",
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'is_paid' => false,
                    'installment_number' => $i,
                    'installment_total' => $installments,
                ]);

                $this->calculateTotal($invoice);
            }
        });
    }

    /**
     * Determine which invoice month a transaction date falls into
     * based on the card's closing day.
     */
    public function getInvoiceMonth(CreditCard $card, Carbon $date): Carbon
    {
        // If the purchase date is after the closing day, it goes to the next month's invoice
        if ($date->day > $card->closing_day) {
            return $date->copy()->startOfMonth()->addMonth();
        }

        return $date->copy()->startOfMonth();
    }

    public function getCurrentInvoiceAmount(CreditCard $card): float
    {
        $now = Carbon::now();
        $invoiceMonth = $this->getInvoiceMonth($card, $now);
        $invoice = CreditCardInvoice::where('credit_card_id', $card->id)
            ->where('reference_month', $invoiceMonth->format('Y-m-d'))
            ->first();

        return $invoice ? (float) $invoice->total_amount : 0;
    }

    private function getOrCreateCardCategory(): \App\Models\Category
    {
        return \App\Models\Category::firstOrCreate(
            ['name' => 'Fatura Cartao', 'type' => TransactionType::Expense],
            ['icon' => 'credit_card', 'color' => '#78909C'],
        );
    }

    private function getDefaultAccountId(): ?string
    {
        return \App\Models\Account::where('is_active', true)->first()?->id;
    }
}
