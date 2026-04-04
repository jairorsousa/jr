<?php

namespace App\Livewire\Crm;

use App\Enums\ActivityType;
use App\Enums\DealStage;
use App\Enums\DealStatus;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Product;
use App\Models\WhatsAppConversation;
use Livewire\Component;

class Negocio extends Component
{
    public string $dealId;
    public ?Deal $deal = null;

    // Edit deal form
    public bool $showEditModal = false;
    public string $dealTitle = '';
    public string $dealContactId = '';
    public string $dealProductId = '';
    public string $dealStage = '';
    public string $dealValue = '0';
    public string $dealExpectedCloseDate = '';
    public string $dealNotes = '';

    // Activity form
    public bool $showActivityModal = false;
    public string $activityType = 'note';
    public string $activityDescription = '';
    public string $activityDate = '';

    // Delete confirmation
    public bool $showDeleteModal = false;

    // Quick stage change
    public string $newStage = '';

    public function mount(string $dealId): void
    {
        $this->dealId = $dealId;
        $this->loadDeal();
    }

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

    private function loadDeal(): void
    {
        $this->deal = Deal::with(['contact', 'product', 'activities' => function ($q) {
            $q->orderByDesc('happened_at');
        }])->findOrFail($this->dealId);
    }

    public function openEditModal(): void
    {
        $this->dealTitle = $this->deal->title;
        $this->dealContactId = $this->deal->contact_id;
        $this->dealProductId = $this->deal->product_id ?? '';
        $this->dealStage = $this->deal->stage->value;
        $this->dealValue = (string) $this->deal->value;
        $this->dealExpectedCloseDate = $this->deal->expected_close_date?->format('Y-m-d') ?? '';
        $this->dealNotes = $this->deal->notes ?? '';
        $this->showEditModal = true;
    }

    public function saveDeal(): void
    {
        $this->validate();

        $oldStage = $this->deal->stage->value;

        $this->deal->update([
            'title' => $this->dealTitle,
            'contact_id' => $this->dealContactId,
            'product_id' => $this->dealProductId ?: null,
            'stage' => $this->dealStage,
            'value' => (float) $this->dealValue,
            'expected_close_date' => $this->dealExpectedCloseDate ?: null,
            'notes' => $this->dealNotes ?: null,
        ]);

        if ($oldStage !== $this->dealStage) {
            $this->logStageChange($oldStage, $this->dealStage);
        }

        $this->showEditModal = false;
        $this->loadDeal();
        session()->flash('success', 'Negocio atualizado com sucesso.');
    }

    public function openActivityModal(): void
    {
        $this->activityType = 'note';
        $this->activityDescription = '';
        $this->activityDate = now()->format('Y-m-d\TH:i');
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityType' => 'required|string',
            'activityDescription' => 'required|string|max:2000',
            'activityDate' => 'required|date',
        ]);

        DealActivity::create([
            'deal_id' => $this->dealId,
            'type' => $this->activityType,
            'description' => $this->activityDescription,
            'happened_at' => $this->activityDate,
        ]);

        $this->showActivityModal = false;
        $this->loadDeal();
        session()->flash('success', 'Atividade registrada com sucesso.');
    }

    public function changeStage(string $stage): void
    {
        $oldStage = $this->deal->stage->value;

        if ($stage === 'won') {
            $this->deal->update([
                'stage' => DealStage::Won,
                'status' => DealStatus::Won,
                'closed_at' => now(),
            ]);
        } elseif ($stage === 'lost') {
            $this->deal->update([
                'stage' => DealStage::Lost,
                'status' => DealStatus::Lost,
                'closed_at' => now(),
            ]);
        } else {
            $this->deal->update(['stage' => $stage]);
        }

        if ($oldStage !== $stage) {
            $this->logStageChange($oldStage, $stage);
        }

        $this->loadDeal();
        session()->flash('success', 'Etapa atualizada com sucesso.');
    }

    public function reopenDeal(): void
    {
        $oldStage = $this->deal->stage->value;

        $this->deal->update([
            'stage' => DealStage::Lead,
            'status' => DealStatus::Open,
            'closed_at' => null,
        ]);

        DealActivity::create([
            'deal_id' => $this->dealId,
            'type' => ActivityType::StageChange,
            'description' => 'Negocio reaberto (de ' . DealStage::from($oldStage)->label() . ' para Lead)',
            'happened_at' => now(),
        ]);

        $this->loadDeal();
        session()->flash('success', 'Negocio reaberto com sucesso.');
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteDeal(): void
    {
        $this->deal->delete();
        session()->flash('success', 'Negocio excluido com sucesso.');
        $this->redirect(route('crm.pipeline'), navigate: true);
    }

    public function deleteActivity(string $activityId): void
    {
        DealActivity::where('id', $activityId)
            ->where('deal_id', $this->dealId)
            ->delete();

        $this->loadDeal();
        session()->flash('success', 'Atividade removida.');
    }

    private function logStageChange(string $from, string $to): void
    {
        $fromLabel = DealStage::from($from)->label();
        $toLabel = DealStage::from($to)->label();

        DealActivity::create([
            'deal_id' => $this->dealId,
            'type' => ActivityType::StageChange,
            'description' => "Movido de {$fromLabel} para {$toLabel}",
            'happened_at' => now(),
        ]);
    }

    public function render()
    {
        $contacts = Contact::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $stages = DealStage::cases();

        $whatsappConversations = WhatsAppConversation::where('deal_id', $this->dealId)
            ->orWhere(function ($q) {
                if ($this->deal?->contact_id) {
                    $q->where('contact_id', $this->deal->contact_id);
                }
            })
            ->with('instance')
            ->orderByDesc('last_message_at')
            ->get();

        return view('livewire.crm.negocio', compact('contacts', 'products', 'stages', 'whatsappConversations'));
    }
}
