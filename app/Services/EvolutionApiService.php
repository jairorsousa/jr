<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.url', ''), '/');
        $this->apiKey = config('services.evolution.api_key', '');
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30);
    }

    // Instance Management

    public function createInstance(string $instanceName, array $options = []): array
    {
        $payload = array_merge([
            'instanceName' => $instanceName,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
        ], $options);

        $response = $this->request()->post('/instance/create', $payload);

        return $this->handleResponse($response);
    }

    public function deleteInstance(string $instanceName): array
    {
        $response = $this->request()->delete("/instance/delete/{$instanceName}");

        return $this->handleResponse($response);
    }

    public function getInstanceStatus(string $instanceName): array
    {
        $response = $this->request()->get("/instance/connectionState/{$instanceName}");

        return $this->handleResponse($response);
    }

    public function connectInstance(string $instanceName): array
    {
        $response = $this->request()->get("/instance/connect/{$instanceName}");

        return $this->handleResponse($response);
    }

    public function disconnectInstance(string $instanceName): array
    {
        $response = $this->request()->delete("/instance/logout/{$instanceName}");

        return $this->handleResponse($response);
    }

    public function restartInstance(string $instanceName): array
    {
        $response = $this->request()->put("/instance/restart/{$instanceName}");

        return $this->handleResponse($response);
    }

    public function fetchInstances(): array
    {
        $response = $this->request()->get('/instance/fetchInstances');

        return $this->handleResponse($response);
    }

    // Webhook

    public function setWebhook(string $instanceName, string $webhookUrl): array
    {
        $response = $this->request()->post("/webhook/set/{$instanceName}", [
            'webhook' => [
                'enabled' => true,
                'url' => $webhookUrl,
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

        return $this->handleResponse($response);
    }

    // Messages

    public function sendText(string $instanceName, string $number, string $text): array
    {
        $response = $this->request()->post("/message/sendText/{$instanceName}", [
            'number' => $number,
            'text' => $text,
        ]);

        return $this->handleResponse($response);
    }

    public function sendMedia(string $instanceName, string $number, string $mediaType, string $url, ?string $caption = null): array
    {
        $response = $this->request()->post("/message/sendMedia/{$instanceName}", [
            'number' => $number,
            'mediatype' => $mediaType,
            'media' => $url,
            'caption' => $caption,
        ]);

        return $this->handleResponse($response);
    }

    public function sendDocument(string $instanceName, string $number, string $url, string $fileName): array
    {
        $response = $this->request()->post("/message/sendMedia/{$instanceName}", [
            'number' => $number,
            'mediatype' => 'document',
            'media' => $url,
            'fileName' => $fileName,
        ]);

        return $this->handleResponse($response);
    }

    // Contacts & Profile

    public function fetchProfilePicture(string $instanceName, string $number): array
    {
        $response = $this->request()->post("/chat/fetchProfilePictureUrl/{$instanceName}", [
            'number' => $number,
        ]);

        return $this->handleResponse($response);
    }

    public function isOnWhatsApp(string $instanceName, array $numbers): array
    {
        $response = $this->request()->post("/chat/whatsappNumbers/{$instanceName}", [
            'numbers' => $numbers,
        ]);

        return $this->handleResponse($response);
    }

    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('message', 'Erro na comunicacao com a Evolution API'),
            'status' => $response->status(),
        ];
    }
}
