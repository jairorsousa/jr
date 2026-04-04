<?php

namespace App\Livewire\WhatsApp;

use App\Enums\ActivityType;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use App\Services\EvolutionApiService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public ?string $instanceId = null;
    public ?string $conversationId = null;
    public string $search = '';
    public string $messageText = '';
    public bool $showNewChatModal = false;
    public bool $showCrmPanel = false;

    // New chat form
    public string $newChatPhone = '';
    public string $newChatName = '';

    // Media upload
    public $mediaFile = null;
    public string $mediaCaption = '';
    public bool $showMediaPreview = false;

    // CRM linking
    public bool $showLinkContactModal = false;
    public bool $showLinkDealModal = false;
    public bool $showCreateContactModal = false;
    public string $linkContactSearch = '';
    public string $linkDealSearch = '';

    // Quick create contact form
    public string $newContactName = '';
    public string $newContactEmail = '';
    public string $newContactCompany = '';

    public function mount(?string $instanceId = null): void
    {
        if ($instanceId) {
            $this->instanceId = $instanceId;
        } else {
            $first = WhatsAppInstance::where('status', 'connected')->first()
                ?? WhatsAppInstance::first();
            $this->instanceId = $first?->id;
        }
    }

    #[On('echo-message-received')]
    public function onNewMessage(array $data = []): void
    {
    }

    #[On('echo-status-updated')]
    public function onStatusUpdated(array $data = []): void
    {
    }

    public function selectInstance(string $id): void
    {
        $this->instanceId = $id;
        $this->conversationId = null;
        $this->showCrmPanel = false;
    }

    public function selectConversation(string $id): void
    {
        $this->conversationId = $id;

        $conversation = WhatsAppConversation::find($id);
        if ($conversation) {
            $conversation->update(['unread_count' => 0]);
        }
    }

    public function toggleCrmPanel(): void
    {
        $this->showCrmPanel = !$this->showCrmPanel;
    }

    // ── CRM: Link Contact ──────────────────────────────────────────

    public function openLinkContactModal(): void
    {
        $this->linkContactSearch = '';
        $this->showLinkContactModal = true;
    }

    public function linkContact(string $contactId): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $contact = Contact::findOrFail($contactId);

        $conversation->update(['contact_id' => $contact->id]);
        $this->showLinkContactModal = false;
    }

    public function unlinkContact(): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $conversation->update(['contact_id' => null]);
    }

    public function autoLinkContact(): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $phone = $conversation->contact_phone;

        // Try to find contact by phone (with partial match for different formats)
        $contact = Contact::where('phone', 'like', '%' . substr($phone, -9) . '%')->first();

        if ($contact) {
            $conversation->update(['contact_id' => $contact->id]);
            session()->flash('success', "Contato '{$contact->name}' vinculado automaticamente.");
        } else {
            session()->flash('error', 'Nenhum contato encontrado com este numero.');
        }
    }

    public function openCreateContactModal(): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $this->newContactName = $conversation->contact_name ?? '';
        $this->newContactEmail = '';
        $this->newContactCompany = '';
        $this->showCreateContactModal = true;
        $this->showLinkContactModal = false;
    }

    public function createAndLinkContact(): void
    {
        $this->validate([
            'newContactName' => 'required|string|max:255',
            'newContactEmail' => 'nullable|email|max:255',
            'newContactCompany' => 'nullable|string|max:255',
        ]);

        $conversation = WhatsAppConversation::findOrFail($this->conversationId);

        $contact = Contact::create([
            'name' => $this->newContactName,
            'email' => $this->newContactEmail ?: null,
            'phone' => $conversation->contact_phone,
            'company' => $this->newContactCompany ?: null,
            'is_active' => true,
        ]);

        $conversation->update(['contact_id' => $contact->id]);
        $this->showCreateContactModal = false;
        session()->flash('success', "Contato '{$contact->name}' criado e vinculado.");
    }

    // ── CRM: Link Deal ─────────────────────────────────────────────

    public function openLinkDealModal(): void
    {
        $this->linkDealSearch = '';
        $this->showLinkDealModal = true;
    }

    public function linkDeal(string $dealId): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $deal = Deal::findOrFail($dealId);

        $conversation->update(['deal_id' => $deal->id]);

        // Also link contact if not linked yet
        if (!$conversation->contact_id && $deal->contact_id) {
            $conversation->update(['contact_id' => $deal->contact_id]);
        }

        // Log activity on deal
        DealActivity::create([
            'deal_id' => $deal->id,
            'type' => ActivityType::WhatsApp,
            'description' => 'Conversa WhatsApp vinculada (' . $conversation->displayName() . ')',
            'happened_at' => now(),
        ]);

        $this->showLinkDealModal = false;
        session()->flash('success', "Negocio '{$deal->title}' vinculado.");
    }

    public function unlinkDeal(): void
    {
        $conversation = WhatsAppConversation::findOrFail($this->conversationId);
        $conversation->update(['deal_id' => null]);
    }

    // ── Chat Functions ──────────────────────────────────────────────

    public function sendMessage(): void
    {
        if (empty(trim($this->messageText)) || !$this->conversationId) {
            return;
        }

        $conversation = WhatsAppConversation::with(['instance', 'deal'])->findOrFail($this->conversationId);
        $instance = $conversation->instance;
        $service = app(EvolutionApiService::class);

        $result = $service->sendText(
            $instance->instance_name,
            $conversation->remote_jid,
            $this->messageText
        );

        if ($result['success']) {
            $messageId = $result['data']['key']['id'] ?? null;

            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'type' => 'text',
                'body' => $this->messageText,
                'from_me' => true,
                'status' => 'sent',
                'message_at' => now(),
            ]);

            $conversation->update([
                'last_message' => mb_substr($this->messageText, 0, 255),
                'last_message_at' => now(),
            ]);

            // Log activity on linked deal
            if ($conversation->deal_id) {
                DealActivity::create([
                    'deal_id' => $conversation->deal_id,
                    'type' => ActivityType::WhatsApp,
                    'description' => 'Mensagem enviada: ' . mb_substr($this->messageText, 0, 100),
                    'happened_at' => now(),
                ]);
            }

            $this->messageText = '';
        } else {
            session()->flash('error', 'Erro ao enviar mensagem: ' . ($result['error'] ?? 'Erro desconhecido'));
        }
    }

    // ── Media Upload ───────────────────────────────────────────────

    public function updatedMediaFile(): void
    {
        $this->validate([
            'mediaFile' => 'required|file|max:16384', // 16MB max
        ]);

        $this->showMediaPreview = true;
    }

    public function cancelMedia(): void
    {
        $this->mediaFile = null;
        $this->mediaCaption = '';
        $this->showMediaPreview = false;
    }

    public function sendMedia(): void
    {
        if (!$this->mediaFile || !$this->conversationId) {
            return;
        }

        $this->validate([
            'mediaFile' => 'required|file|max:16384',
            'mediaCaption' => 'nullable|string|max:1000',
        ]);

        $conversation = WhatsAppConversation::with(['instance', 'deal'])->findOrFail($this->conversationId);
        $instance = $conversation->instance;
        $service = app(EvolutionApiService::class);

        $file = $this->mediaFile;
        $mime = $file->getMimeType();
        $originalName = $file->getClientOriginalName();

        // Store the file locally
        $path = $file->store('whatsapp-media', 'public');
        $publicUrl = asset('storage/' . $path);

        // Determine media type for Evolution API
        $mediaType = $this->detectMediaType($mime);
        $caption = trim($this->mediaCaption) ?: null;

        if ($mediaType === 'document') {
            $result = $service->sendDocument(
                $instance->instance_name,
                $conversation->remote_jid,
                $publicUrl,
                $originalName
            );
        } else {
            $result = $service->sendMedia(
                $instance->instance_name,
                $conversation->remote_jid,
                $mediaType,
                $publicUrl,
                $caption
            );
        }

        if ($result['success']) {
            $messageId = $result['data']['key']['id'] ?? null;

            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'type' => $mediaType,
                'body' => $caption,
                'media_url' => $publicUrl,
                'media_mimetype' => $mime,
                'media_filename' => $originalName,
                'from_me' => true,
                'status' => 'sent',
                'message_at' => now(),
            ]);

            $lastMsg = $caption
                ? mb_substr($caption, 0, 255)
                : "[" . ucfirst($mediaType) . "]";

            $conversation->update([
                'last_message' => $lastMsg,
                'last_message_at' => now(),
            ]);

            // Log activity on linked deal
            if ($conversation->deal_id) {
                DealActivity::create([
                    'deal_id' => $conversation->deal_id,
                    'type' => ActivityType::WhatsApp,
                    'description' => ucfirst($mediaType) . ' enviado' . ($caption ? ': ' . mb_substr($caption, 0, 80) : ''),
                    'happened_at' => now(),
                ]);
            }

            $this->cancelMedia();
        } else {
            session()->flash('error', 'Erro ao enviar midia: ' . ($result['error'] ?? 'Erro desconhecido'));
        }
    }

    private function detectMediaType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        return 'document';
    }

    public function startNewChat(): void
    {
        if (empty($this->newChatPhone) || !$this->instanceId) {
            return;
        }

        $phone = preg_replace('/\D/', '', $this->newChatPhone);
        $remoteJid = $phone . '@s.whatsapp.net';

        $instance = WhatsAppInstance::findOrFail($this->instanceId);

        // Try auto-linking with existing contact by phone
        $contact = Contact::where('phone', 'like', '%' . substr($phone, -9) . '%')->first();

        $conversation = WhatsAppConversation::firstOrCreate(
            ['instance_id' => $instance->id, 'remote_jid' => $remoteJid],
            [
                'contact_name' => $this->newChatName ?: ($contact?->name),
                'contact_phone' => $phone,
                'contact_id' => $contact?->id,
                'is_group' => false,
            ]
        );

        $this->conversationId = $conversation->id;
        $this->showNewChatModal = false;
        $this->newChatPhone = '';
        $this->newChatName = '';
    }

    public function deleteConversation(string $id): void
    {
        WhatsAppConversation::findOrFail($id)->delete();

        if ($this->conversationId === $id) {
            $this->conversationId = null;
            $this->showCrmPanel = false;
        }
    }

    public function render()
    {
        $instances = WhatsAppInstance::orderBy('name')->get();

        $conversations = collect();
        $messages = collect();
        $activeConversation = null;
        $linkedContact = null;
        $linkedDeal = null;
        $contactDeals = collect();

        if ($this->instanceId) {
            $conversations = WhatsAppConversation::with('contact')
                ->where('instance_id', $this->instanceId)
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('contact_name', 'like', "%{$this->search}%")
                          ->orWhere('contact_phone', 'like', "%{$this->search}%")
                          ->orWhereHas('contact', function ($cq) {
                              $cq->where('name', 'like', "%{$this->search}%")
                                ->orWhere('company', 'like', "%{$this->search}%");
                          });
                    });
                })
                ->orderByDesc('last_message_at')
                ->get();
        }

        if ($this->conversationId) {
            $activeConversation = WhatsAppConversation::with(['instance', 'contact', 'deal.contact'])->find($this->conversationId);
            $messages = WhatsAppMessage::where('conversation_id', $this->conversationId)
                ->orderBy('message_at')
                ->get();

            if ($activeConversation) {
                $linkedContact = $activeConversation->contact;
                $linkedDeal = $activeConversation->deal;

                if ($linkedContact) {
                    $contactDeals = Deal::where('contact_id', $linkedContact->id)
                        ->orderByDesc('created_at')
                        ->limit(5)
                        ->get();
                }
            }
        }

        // Data for link modals
        $linkContacts = collect();
        $linkDeals = collect();

        if ($this->showLinkContactModal) {
            $linkContacts = Contact::where('is_active', true)
                ->when($this->linkContactSearch, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('name', 'like', "%{$this->linkContactSearch}%")
                          ->orWhere('phone', 'like', "%{$this->linkContactSearch}%")
                          ->orWhere('email', 'like', "%{$this->linkContactSearch}%")
                          ->orWhere('company', 'like', "%{$this->linkContactSearch}%");
                    });
                })
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        if ($this->showLinkDealModal) {
            $linkDeals = Deal::with('contact')
                ->where('status', 'open')
                ->when($this->linkDealSearch, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('title', 'like', "%{$this->linkDealSearch}%")
                          ->orWhereHas('contact', function ($cq) {
                              $cq->where('name', 'like', "%{$this->linkDealSearch}%");
                          });
                    });
                })
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return view('livewire.whatsapp.chat', compact(
            'instances',
            'conversations',
            'messages',
            'activeConversation',
            'linkedContact',
            'linkedDeal',
            'contactDeals',
            'linkContacts',
            'linkDeals',
        ));
    }
}
