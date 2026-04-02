<?php

namespace App\Livewire\WhatsApp;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use App\Services\EvolutionApiService;
use Livewire\Component;

class Chat extends Component
{
    public ?string $instanceId = null;
    public ?string $conversationId = null;
    public string $search = '';
    public string $messageText = '';
    public bool $showNewChatModal = false;

    // New chat form
    public string $newChatPhone = '';
    public string $newChatName = '';

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

    public function selectInstance(string $id): void
    {
        $this->instanceId = $id;
        $this->conversationId = null;
    }

    public function selectConversation(string $id): void
    {
        $this->conversationId = $id;

        // Mark as read
        $conversation = WhatsAppConversation::find($id);
        if ($conversation) {
            $conversation->update(['unread_count' => 0]);
        }
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->messageText)) || !$this->conversationId) {
            return;
        }

        $conversation = WhatsAppConversation::with('instance')->findOrFail($this->conversationId);
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

            $this->messageText = '';
        } else {
            session()->flash('error', 'Erro ao enviar mensagem: ' . ($result['error'] ?? 'Erro desconhecido'));
        }
    }

    public function startNewChat(): void
    {
        if (empty($this->newChatPhone) || !$this->instanceId) {
            return;
        }

        $phone = preg_replace('/\D/', '', $this->newChatPhone);
        $remoteJid = $phone . '@s.whatsapp.net';

        $instance = WhatsAppInstance::findOrFail($this->instanceId);

        $conversation = WhatsAppConversation::firstOrCreate(
            ['instance_id' => $instance->id, 'remote_jid' => $remoteJid],
            [
                'contact_name' => $this->newChatName ?: null,
                'contact_phone' => $phone,
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
        }
    }

    public function refreshMessages(): void
    {
        // Livewire will re-render and fetch fresh messages
    }

    public function render()
    {
        $instances = WhatsAppInstance::orderBy('name')->get();

        $conversations = collect();
        $messages = collect();
        $activeConversation = null;

        if ($this->instanceId) {
            $conversations = WhatsAppConversation::where('instance_id', $this->instanceId)
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('contact_name', 'like', "%{$this->search}%")
                          ->orWhere('contact_phone', 'like', "%{$this->search}%");
                    });
                })
                ->orderByDesc('last_message_at')
                ->get();
        }

        if ($this->conversationId) {
            $activeConversation = WhatsAppConversation::with('instance')->find($this->conversationId);
            $messages = WhatsAppMessage::where('conversation_id', $this->conversationId)
                ->orderBy('message_at')
                ->get();
        }

        return view('livewire.whatsapp.chat', compact(
            'instances',
            'conversations',
            'messages',
            'activeConversation',
        ));
    }
}
