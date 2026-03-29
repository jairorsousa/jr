<?php

namespace App\Livewire\Crm;

use App\Enums\ActivityType;
use App\Enums\DealStage;
use App\Enums\DealStatus;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Product;
use Livewire\Component;

class Pipeline extends Component
{
    public bool $showDealModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingDealId = null;
    public ?string $deletingDealId = null;

    // Deal form
    public string $dealTitle = '';
    public string $dealContactId = '';
    public string $dealProductId = '';
    public string $dealStage = 'lead';
    public string $dealValue = '0';
    public string $dealExpectedCloseDate = '';
    public string $dealNotes = '';

    // Filters
    public string $filterProduct = '';
    public string $filterSearch = '';

    protected function rules(): array
    {
        return [
            'dealTitle' => 'required|string|max:255',
            'dealContactId' => 'required|exists:contacts,id',
            'dealProductId' => 'nullable|string',
            'dealStage' => 'required|string',
            'dealValue' => 'required|numeric|min:0',
            'dealExpectedCloseDate' => 'nullable|date',
            'dealNotes' => 'nullable|string',
        ];
    }

    public function openCreateDeal(string $stage = 'lead'): void
    {
        $this->resetDealForm();
        $this->dealStage = $stage;
        $this->showDealModal = true;
    }

    public function openEditDeal(string $id): void
    {
        $deal = Deal::findOrFail($id);
        $this->editingDealId = $id;
        $this->dealTitle = $deal->title;
        $this->dealContactId = $deal->contact_id;
        $this->dealProductId = $deal->product_id ?? '';
        $this->dealStage = $deal->stage->value;
        $this->dealValue = (string) $deal->value;
        $this->dealExpectedCloseDate = $deal->expected_close_date?->format('Y-m-d') ?? '';
        $this->dealNotes = $deal->notes ?? '';
        $this->showDealModal = true;
    }

    public function saveDeal(): void
    {
        $this->validate();

        $data = [
            'title' => $this->dealTitle,
            'contact_id' => $this->dealContactId,
            'product_id' => $this->dealProductId ?: null,
            'stage' => $this->dealStage,
            'value' => (float) $this->dealValue,
            'expected_close_date' => $this->dealExpectedCloseDate ?: null,
            'notes' => $this->dealNotes ?: null,
        ];

        if ($this->editingDealId) {
            $deal = Deal::findOrFail($this->editingDealId);
            $oldStage = $deal->stage->value;
            $deal->update($data);

            if ($oldStage !== $this->dealStage) {
                $this->logStageChange($deal, $oldStage, $this->dealStage);
            }

            session()->flash('success', 'Negocio atualizado com sucesso.');
        } else {
            $data['status'] = 'open';
            $data['sort_order'] = Deal::where('stage', $this->dealStage)->max('sort_order') + 1;
            $deal = Deal::create($data);

            DealActivity::create([
                'deal_id' => $deal->id,
                'type' => ActivityType::StageChange,
                'description' => 'Negocio criado na etapa ' . DealStage::from($this->dealStage)->label(),
                'happened_at' => now(),
            ]);

            session()->flash('success', 'Negocio criado com sucesso.');
        }

        $this->showDealModal = false;
        $this->resetDealForm();
    }

    public function moveDeal(string $dealId, string $newStage, int $newOrder): void
    {
        $deal = Deal::findOrFail($dealId);
        $oldStage = $deal->stage->value;

        if ($newStage === 'won') {
            $deal->update([
                'stage' => DealStage::Won,
                'status' => DealStatus::Won,
                'closed_at' => now(),
                'sort_order' => $newOrder,
            ]);
        } elseif ($newStage === 'lost') {
            $deal->update([
                'stage' => DealStage::Lost,
                'status' => DealStatus::Lost,
                'closed_at' => now(),
                'sort_order' => $newOrder,
            ]);
        } else {
            $deal->update([
                'stage' => $newStage,
                'sort_order' => $newOrder,
            ]);
        }

        if ($oldStage !== $newStage) {
            $this->logStageChange($deal, $oldStage, $newStage);
        }
    }

    public function markAsWon(string $id): void
    {
        $this->moveDeal($id, 'won', 0);
        session()->flash('success', 'Negocio marcado como ganho!');
    }

    public function markAsLost(string $id): void
    {
        $this->moveDeal($id, 'lost', 0);
        session()->flash('success', 'Negocio marcado como perdido.');
    }

    public function confirmDeleteDeal(string $id): void
    {
        $this->deletingDealId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteDeal(): void
    {
        Deal::findOrFail($this->deletingDealId)->delete();
        session()->flash('success', 'Negocio excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingDealId = null;
    }

    private function logStageChange(Deal $deal, string $from, string $to): void
    {
        $fromLabel = DealStage::from($from)->label();
        $toLabel = DealStage::from($to)->label();

        DealActivity::create([
            'deal_id' => $deal->id,
            'type' => ActivityType::StageChange,
            'description' => "Movido de {$fromLabel} para {$toLabel}",
            'happened_at' => now(),
        ]);
    }

    private function resetDealForm(): void
    {
        $this->reset(['dealTitle', 'dealContactId', 'dealProductId', 'dealValue', 'dealExpectedCloseDate', 'dealNotes', 'editingDealId']);
        $this->dealStage = 'lead';
        $this->dealValue = '0';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Deal::with(['contact', 'product'])
            ->where('status', DealStatus::Open);

        if ($this->filterProduct) {
            $query->where('product_id', $this->filterProduct);
        }
        if ($this->filterSearch) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->filterSearch}%")
                  ->orWhereHas('contact', fn ($cq) => $cq->where('name', 'like', "%{$this->filterSearch}%"));
            });
        }

        $deals = $query->orderBy('sort_order')->get();

        $stages = DealStage::pipelineStages();
        $dealsByStage = [];
        foreach ($stages as $stage) {
            $dealsByStage[$stage->value] = $deals->where('stage', $stage);
        }

        $totalPipelineValue = $deals->sum('value');
        $totalDeals = $deals->count();
        $wonThisMonth = Deal::where('status', DealStatus::Won)
            ->whereMonth('closed_at', now()->month)
            ->whereYear('closed_at', now()->year)
            ->sum('value');

        $contacts = Contact::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('livewire.crm.pipeline', compact(
            'stages', 'dealsByStage', 'totalPipelineValue', 'totalDeals',
            'wonThisMonth', 'contacts', 'products',
        ));
    }
}
