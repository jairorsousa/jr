<?php

namespace App\Livewire\Financeiro;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Livewire\Component;

class Fatura extends Component
{
    public string $cardId;
    public string $currentMonth;

    public function mount(string $id): void
    {
        $this->cardId = $id;
        $this->currentMonth = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
    }

    public function closeInvoice(): void
    {
        $card = CreditCard::findOrFail($this->cardId);
        $ref = Carbon::parse($this->currentMonth . '-01');
        $invoice = app(InvoiceService::class)->getOrCreateInvoice($card, $ref);

        app(InvoiceService::class)->closeInvoice($invoice);
        session()->flash('success', 'Fatura fechada com sucesso.');
    }

    public function reopenInvoice(): void
    {
        $card = CreditCard::findOrFail($this->cardId);
        $ref = Carbon::parse($this->currentMonth . '-01');
        $invoice = app(InvoiceService::class)->getOrCreateInvoice($card, $ref);

        app(InvoiceService::class)->reopenInvoice($invoice);
        session()->flash('success', 'Fatura reaberta.');
    }

    public function payInvoice(): void
    {
        $card = CreditCard::findOrFail($this->cardId);
        $ref = Carbon::parse($this->currentMonth . '-01');
        $invoice = app(InvoiceService::class)->getOrCreateInvoice($card, $ref);

        try {
            app(InvoiceService::class)->payInvoice($invoice);
            session()->flash('success', 'Fatura paga com sucesso. Transacao de pagamento criada na conta vinculada.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $card = CreditCard::with('account')->findOrFail($this->cardId);
        $ref = Carbon::parse($this->currentMonth . '-01');

        $invoice = CreditCardInvoice::where('credit_card_id', $card->id)
            ->where('reference_month', $ref->format('Y-m-d'))
            ->first();

        $transactions = [];
        $totalAmount = 0;

        if ($invoice) {
            $transactions = $invoice->transactions()
                ->with('category')
                ->orderBy('date')
                ->get();
            $totalAmount = (float) $invoice->total_amount;
        }

        $monthLabel = ucfirst($ref->translatedFormat('F Y'));

        return view('livewire.financeiro.fatura', compact(
            'card', 'invoice', 'transactions', 'totalAmount', 'monthLabel'
        ));
    }
}
