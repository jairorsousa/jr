<?php

namespace App\Livewire\WhatsApp;

use App\Enums\InstanceStatus;
use App\Models\WhatsAppInstance;
use App\Services\EvolutionApiService;
use Livewire\Attributes\On;
use Livewire\Component;

class Instancias extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showQrModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public ?string $connectingId = null;

    // Form fields
    public string $name = '';
    public string $instance_name = '';

    // QR Code
    public ?string $qrcode = null;
    public ?string $qrInstanceName = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'instance_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
        ];
    }

    protected $messages = [
        'instance_name.regex' => 'O nome da instancia deve conter apenas letras, numeros, hifens e underscores.',
    ];

    /**
     * Called from frontend Echo listener when instance connection changes
     */
    #[On('echo-connection-updated')]
    public function onConnectionUpdated(array $data = []): void
    {
        $instanceData = $data['instance'] ?? [];
        $status = $instanceData['status'] ?? null;

        // If connecting and QR modal is open, update QR code
        if ($this->showQrModal && $this->connectingId) {
            $instance = WhatsAppInstance::find($this->connectingId);
            if ($instance) {
                if ($instance->status === InstanceStatus::Connected) {
                    $this->showQrModal = false;
                    $this->qrcode = null;
                    session()->flash('success', 'WhatsApp conectado com sucesso!');
                } elseif ($instance->qrcode) {
                    $this->qrcode = $instance->qrcode;
                }
            }
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $instance = WhatsAppInstance::findOrFail($id);
        $this->editingId = $id;
        $this->name = $instance->name;
        $this->instance_name = $instance->instance_name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $service = app(EvolutionApiService::class);

        if ($this->editingId) {
            $instance = WhatsAppInstance::findOrFail($this->editingId);
            $instance->update([
                'name' => $this->name,
            ]);
            session()->flash('success', 'Instancia atualizada com sucesso.');
        } else {
            // Create instance in Evolution API
            $result = $service->createInstance($this->instance_name, [
                'webhook' => [
                    'enabled' => true,
                    'url' => config('app.url') . '/api/whatsapp/webhook',
                    'webhookByEvents' => false,
                    'webhookBase64' => false,
                    'events' => [
                        'MESSAGES_UPSERT',
                        'MESSAGES_UPDATE',
                        'CONNECTION_UPDATE',
                        'QRCODE_UPDATED',
                    ],
                ],
            ]);

            if (!$result['success']) {
                session()->flash('error', 'Erro ao criar instancia: ' . ($result['error'] ?? 'Erro desconhecido'));
                return;
            }

            $data = $result['data'] ?? [];
            $qrcode = $data['qrcode']['base64'] ?? null;

            $instance = WhatsAppInstance::create([
                'name' => $this->name,
                'instance_name' => $this->instance_name,
                'status' => $qrcode ? InstanceStatus::Connecting : InstanceStatus::Disconnected,
                'qrcode' => $qrcode,
            ]);

            if ($qrcode) {
                $this->showModal = false;
                $this->qrcode = $qrcode;
                $this->qrInstanceName = $instance->instance_name;
                $this->connectingId = $instance->id;
                $this->showQrModal = true;
                $this->resetForm();
                return;
            }

            session()->flash('success', 'Instancia criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function connect(string $id): void
    {
        $instance = WhatsAppInstance::findOrFail($id);
        $service = app(EvolutionApiService::class);

        $result = $service->connectInstance($instance->instance_name);

        if (!$result['success']) {
            session()->flash('error', 'Erro ao conectar: ' . ($result['error'] ?? 'Erro desconhecido'));
            return;
        }

        $data = $result['data'] ?? [];
        $qrcode = $data['base64'] ?? null;

        if ($qrcode) {
            $instance->update([
                'status' => InstanceStatus::Connecting,
                'qrcode' => $qrcode,
            ]);
            $this->qrcode = $qrcode;
            $this->qrInstanceName = $instance->instance_name;
            $this->connectingId = $instance->id;
            $this->showQrModal = true;
        }
    }

    public function refreshQrCode(): void
    {
        if (!$this->connectingId) return;

        $instance = WhatsAppInstance::find($this->connectingId);
        if (!$instance) return;

        // Check if already connected
        if ($instance->status === InstanceStatus::Connected) {
            $this->showQrModal = false;
            $this->qrcode = null;
            session()->flash('success', 'WhatsApp conectado com sucesso!');
            return;
        }

        // If there's a fresh QR from webhook
        if ($instance->qrcode) {
            $this->qrcode = $instance->qrcode;
        }
    }

    public function checkConnection(): void
    {
        if (!$this->connectingId) return;

        $instance = WhatsAppInstance::find($this->connectingId);
        if (!$instance) return;

        $service = app(EvolutionApiService::class);
        $result = $service->getInstanceStatus($instance->instance_name);

        if ($result['success']) {
            $state = $result['data']['instance']['state'] ?? 'close';
            if ($state === 'open') {
                $instance->update([
                    'status' => InstanceStatus::Connected,
                    'qrcode' => null,
                    'connected_at' => now(),
                ]);
                $this->showQrModal = false;
                $this->qrcode = null;
                session()->flash('success', 'WhatsApp conectado com sucesso!');
            }
        }
    }

    public function disconnect(string $id): void
    {
        $instance = WhatsAppInstance::findOrFail($id);
        $service = app(EvolutionApiService::class);

        $result = $service->disconnectInstance($instance->instance_name);

        $instance->update([
            'status' => InstanceStatus::Disconnected,
            'qrcode' => null,
        ]);

        session()->flash('success', 'Instancia desconectada.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $instance = WhatsAppInstance::findOrFail($this->deletingId);
        $service = app(EvolutionApiService::class);

        $service->deleteInstance($instance->instance_name);
        $instance->delete();

        session()->flash('success', 'Instancia excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function syncStatus(): void
    {
        $service = app(EvolutionApiService::class);
        $instances = WhatsAppInstance::all();

        foreach ($instances as $instance) {
            $result = $service->getInstanceStatus($instance->instance_name);
            if ($result['success']) {
                $state = $result['data']['instance']['state'] ?? 'close';
                $instance->update([
                    'status' => $state === 'open' ? InstanceStatus::Connected : InstanceStatus::Disconnected,
                ]);
            }
        }

        session()->flash('success', 'Status sincronizado.');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'instance_name', 'editingId']);
        $this->resetValidation();
    }

    public function render()
    {
        $instances = WhatsAppInstance::withCount('conversations')
            ->orderBy('name')
            ->get();

        return view('livewire.whatsapp.instancias', compact('instances'));
    }
}
