<?php

namespace App\Livewire\WhatsApp;

use App\Enums\CampaignStatus;
use App\Jobs\ProcessWhatsAppCampaign;
use App\Models\Contact;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppTemplate;
use Livewire\Component;

class Campanhas extends Component
{
    public string $search = '';
    public string $filterStatus = '';

    // Campaign form modal
    public bool $showModal = false;
    public ?string $editingId = null;
    public string $campaignName = '';
    public string $campaignInstanceId = '';
    public string $campaignTemplateId = '';
    public string $campaignCustomMessage = '';
    public int $campaignDelay = 5;
    public string $messageSource = 'template'; // 'template' or 'custom'

    // Recipients modal
    public bool $showRecipientsModal = false;
    public ?string $recipientsCampaignId = null;
    public string $addPhone = '';
    public string $addName = '';
    public string $recipientSource = 'manual'; // 'manual', 'contacts', 'csv'
    public string $contactFilter = '';
    public array $selectedContacts = [];
    public string $csvPhones = '';

    // Detail / progress modal
    public bool $showDetailModal = false;
    public ?WhatsAppCampaign $detailCampaign = null;

    // Delete
    public bool $showDeleteModal = false;
    public ?string $deletingId = null;

    protected function rules(): array
    {
        $rules = [
            'campaignName' => 'required|string|max:255',
            'campaignInstanceId' => 'required|exists:whatsapp_instances,id',
            'campaignDelay' => 'required|integer|min:1|max:60',
        ];

        if ($this->messageSource === 'template') {
            $rules['campaignTemplateId'] = 'required|exists:whatsapp_message_templates,id';
        } else {
            $rules['campaignCustomMessage'] = 'required|string|max:4000';
        }

        return $rules;
    }

    // ── Campaign CRUD ───────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);

        if (!$campaign->isDraft()) {
            session()->flash('error', 'Somente campanhas em rascunho podem ser editadas.');
            return;
        }

        $this->editingId = $id;
        $this->campaignName = $campaign->name;
        $this->campaignInstanceId = $campaign->instance_id;
        $this->campaignTemplateId = $campaign->template_id ?? '';
        $this->campaignCustomMessage = $campaign->custom_message ?? '';
        $this->campaignDelay = $campaign->delay_seconds;
        $this->messageSource = $campaign->template_id ? 'template' : 'custom';
        $this->showModal = true;
    }

    public function saveCampaign(): void
    {
        $this->validate();

        $data = [
            'name' => $this->campaignName,
            'instance_id' => $this->campaignInstanceId,
            'template_id' => $this->messageSource === 'template' ? $this->campaignTemplateId : null,
            'custom_message' => $this->messageSource === 'custom' ? $this->campaignCustomMessage : null,
            'delay_seconds' => $this->campaignDelay,
        ];

        if ($this->editingId) {
            $campaign = WhatsAppCampaign::findOrFail($this->editingId);
            $campaign->update($data);
            session()->flash('success', 'Campanha atualizada com sucesso.');
        } else {
            $data['status'] = CampaignStatus::Draft;
            WhatsAppCampaign::create($data);
            session()->flash('success', 'Campanha criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCampaign(): void
    {
        $campaign = WhatsAppCampaign::findOrFail($this->deletingId);

        if ($campaign->isSending()) {
            session()->flash('error', 'Nao e possivel excluir uma campanha que esta sendo enviada.');
            $this->showDeleteModal = false;
            return;
        }

        $campaign->delete();
        session()->flash('success', 'Campanha excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    // ── Recipients ──────────────────────────────────────────────────

    public function openRecipientsModal(string $campaignId): void
    {
        $this->recipientsCampaignId = $campaignId;
        $this->addPhone = '';
        $this->addName = '';
        $this->recipientSource = 'manual';
        $this->selectedContacts = [];
        $this->csvPhones = '';
        $this->contactFilter = '';
        $this->showRecipientsModal = true;
    }

    public function addRecipientManual(): void
    {
        $this->validate([
            'addPhone' => 'required|string|min:10|max:20',
        ]);

        $phone = preg_replace('/\D/', '', $this->addPhone);

        $exists = WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)
            ->where('phone', $phone)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Este numero ja esta na campanha.');
            return;
        }

        WhatsAppCampaignRecipient::create([
            'campaign_id' => $this->recipientsCampaignId,
            'phone' => $phone,
            'name' => $this->addName ?: null,
        ]);

        $this->updateRecipientCount();
        $this->addPhone = '';
        $this->addName = '';
    }

    public function addContactsAsBulk(): void
    {
        if (empty($this->selectedContacts)) {
            session()->flash('error', 'Selecione pelo menos um contato.');
            return;
        }

        $contacts = Contact::whereIn('id', $this->selectedContacts)
            ->whereNotNull('phone')
            ->get();

        $added = 0;
        foreach ($contacts as $contact) {
            $phone = preg_replace('/\D/', '', $contact->phone);

            $exists = WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)
                ->where('phone', $phone)
                ->exists();

            if (!$exists) {
                WhatsAppCampaignRecipient::create([
                    'campaign_id' => $this->recipientsCampaignId,
                    'contact_id' => $contact->id,
                    'phone' => $phone,
                    'name' => $contact->name,
                    'variables' => [
                        'nome' => $contact->name,
                        'empresa' => $contact->company ?? '',
                        'email' => $contact->email ?? '',
                    ],
                ]);
                $added++;
            }
        }

        $this->updateRecipientCount();
        $this->selectedContacts = [];
        session()->flash('success', "{$added} contato(s) adicionado(s) a campanha.");
    }

    public function importCsvPhones(): void
    {
        if (empty(trim($this->csvPhones))) {
            session()->flash('error', 'Cole os numeros de telefone.');
            return;
        }

        $lines = preg_split('/[\n,;]+/', $this->csvPhones);
        $added = 0;

        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', trim($line)));
            $phone = preg_replace('/\D/', '', $parts[0] ?? '');
            $name = $parts[1] ?? null;

            if (strlen($phone) < 10) continue;

            $exists = WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)
                ->where('phone', $phone)
                ->exists();

            if (!$exists) {
                WhatsAppCampaignRecipient::create([
                    'campaign_id' => $this->recipientsCampaignId,
                    'phone' => $phone,
                    'name' => $name,
                ]);
                $added++;
            }
        }

        $this->updateRecipientCount();
        $this->csvPhones = '';
        session()->flash('success', "{$added} destinatario(s) importado(s).");
    }

    public function removeRecipient(string $id): void
    {
        WhatsAppCampaignRecipient::where('id', $id)
            ->where('campaign_id', $this->recipientsCampaignId)
            ->delete();

        $this->updateRecipientCount();
    }

    public function removeAllRecipients(): void
    {
        WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)->delete();
        $this->updateRecipientCount();
        session()->flash('success', 'Todos os destinatarios foram removidos.');
    }

    private function updateRecipientCount(): void
    {
        $count = WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)->count();
        WhatsAppCampaign::where('id', $this->recipientsCampaignId)
            ->update(['total_recipients' => $count]);
    }

    // ── Campaign Actions ────────────────────────────────────────────

    public function startCampaign(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);

        if (!$campaign->isDraft() && !$campaign->isPaused()) {
            session()->flash('error', 'Esta campanha nao pode ser iniciada.');
            return;
        }

        if ($campaign->total_recipients === 0) {
            session()->flash('error', 'Adicione pelo menos um destinatario antes de iniciar.');
            return;
        }

        // Verify instance is connected
        if (!$campaign->instance || $campaign->instance->status->value !== 'connected') {
            session()->flash('error', 'A instancia WhatsApp nao esta conectada.');
            return;
        }

        $campaign->update([
            'status' => CampaignStatus::Sending,
            'started_at' => $campaign->started_at ?? now(),
        ]);

        ProcessWhatsAppCampaign::dispatch($campaign->id);

        session()->flash('success', 'Campanha iniciada! As mensagens estao sendo enviadas.');
    }

    public function pauseCampaign(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);
        if ($campaign->isSending()) {
            $campaign->update(['status' => CampaignStatus::Paused]);
            session()->flash('success', 'Campanha pausada.');
        }
    }

    public function resumeCampaign(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);
        if ($campaign->isPaused()) {
            $campaign->update(['status' => CampaignStatus::Sending]);
            ProcessWhatsAppCampaign::dispatch($campaign->id);
            session()->flash('success', 'Campanha retomada.');
        }
    }

    public function cancelCampaign(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);
        if ($campaign->isSending() || $campaign->isPaused()) {
            $campaign->update([
                'status' => CampaignStatus::Cancelled,
                'completed_at' => now(),
            ]);
            session()->flash('success', 'Campanha cancelada.');
        }
    }

    public function openDetailModal(string $id): void
    {
        $this->detailCampaign = WhatsAppCampaign::with(['instance', 'template', 'recipients' => function ($q) {
            $q->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'sent' THEN 1 ELSE 2 END")
              ->orderByDesc('sent_at');
        }])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function duplicateCampaign(string $id): void
    {
        $campaign = WhatsAppCampaign::findOrFail($id);
        $newCampaign = WhatsAppCampaign::create([
            'name' => $campaign->name . ' (Copia)',
            'instance_id' => $campaign->instance_id,
            'template_id' => $campaign->template_id,
            'custom_message' => $campaign->custom_message,
            'delay_seconds' => $campaign->delay_seconds,
            'status' => CampaignStatus::Draft,
        ]);

        // Copy recipients
        $recipients = $campaign->recipients;
        foreach ($recipients as $recipient) {
            WhatsAppCampaignRecipient::create([
                'campaign_id' => $newCampaign->id,
                'contact_id' => $recipient->contact_id,
                'phone' => $recipient->phone,
                'name' => $recipient->name,
                'variables' => $recipient->variables,
            ]);
        }

        $newCampaign->update(['total_recipients' => $recipients->count()]);

        session()->flash('success', 'Campanha duplicada com sucesso.');
    }

    private function resetForm(): void
    {
        $this->reset(['campaignName', 'campaignInstanceId', 'campaignTemplateId', 'campaignCustomMessage', 'editingId']);
        $this->campaignDelay = 5;
        $this->messageSource = 'template';
        $this->resetValidation();
    }

    public function render()
    {
        $campaigns = WhatsAppCampaign::with(['instance', 'template'])
            ->withCount(['recipients', 'sentRecipients', 'failedRecipients'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderByDesc('created_at')
            ->get();

        $instances = WhatsAppInstance::where('status', 'connected')->orderBy('name')->get();
        $templates = WhatsAppTemplate::where('is_active', true)->orderBy('name')->get();
        $statuses = CampaignStatus::cases();

        // For recipients modal
        $recipients = collect();
        $availableContacts = collect();
        if ($this->showRecipientsModal && $this->recipientsCampaignId) {
            $recipients = WhatsAppCampaignRecipient::where('campaign_id', $this->recipientsCampaignId)
                ->orderBy('name')
                ->get();

            if ($this->recipientSource === 'contacts') {
                $availableContacts = Contact::where('is_active', true)
                    ->whereNotNull('phone')
                    ->when($this->contactFilter, function ($q) {
                        $q->where(function ($sq) {
                            $sq->where('name', 'like', "%{$this->contactFilter}%")
                               ->orWhere('phone', 'like', "%{$this->contactFilter}%")
                               ->orWhere('company', 'like', "%{$this->contactFilter}%");
                        });
                    })
                    ->orderBy('name')
                    ->limit(50)
                    ->get();
            }
        }

        return view('livewire.whatsapp.campanhas', compact(
            'campaigns',
            'instances',
            'templates',
            'statuses',
            'recipients',
            'availableContacts',
        ));
    }
}
