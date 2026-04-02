<?php

namespace App\Http\Controllers;

use App\Enums\InstanceStatus;
use App\Enums\MessageType;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();
        $event = $data['event'] ?? null;
        $instanceName = $data['instance'] ?? null;

        if (!$event || !$instanceName) {
            return response()->json(['status' => 'ignored']);
        }

        Log::info("WhatsApp Webhook: {$event}", ['instance' => $instanceName]);

        $instance = WhatsAppInstance::where('instance_name', $instanceName)->first();

        if (!$instance) {
            return response()->json(['status' => 'instance_not_found']);
        }

        return match ($event) {
            'messages.upsert' => $this->handleMessagesUpsert($instance, $data),
            'messages.update' => $this->handleMessagesUpdate($instance, $data),
            'connection.update' => $this->handleConnectionUpdate($instance, $data),
            'qrcode.updated' => $this->handleQrcodeUpdated($instance, $data),
            default => response()->json(['status' => 'event_not_handled']),
        };
    }

    private function handleMessagesUpsert(WhatsAppInstance $instance, array $data): JsonResponse
    {
        $messageData = $data['data'] ?? [];

        $key = $messageData['key'] ?? [];
        $remoteJid = $key['remoteJid'] ?? null;
        $fromMe = $key['fromMe'] ?? false;
        $messageId = $key['id'] ?? null;

        if (!$remoteJid) {
            return response()->json(['status' => 'no_jid']);
        }

        // Skip status messages
        if (str_contains($remoteJid, 'status@broadcast')) {
            return response()->json(['status' => 'ignored_status']);
        }

        $phone = $this->extractPhone($remoteJid);
        $isGroup = str_contains($remoteJid, '@g.us');
        $pushName = $messageData['pushName'] ?? null;

        // Determine message type and body
        $message = $messageData['message'] ?? [];
        [$type, $body, $mediaUrl, $mimetype, $filename] = $this->parseMessage($message, $messageData);

        // Find or create conversation
        $conversation = WhatsAppConversation::firstOrCreate(
            ['instance_id' => $instance->id, 'remote_jid' => $remoteJid],
            [
                'contact_name' => $pushName,
                'contact_phone' => $phone,
                'is_group' => $isGroup,
            ]
        );

        // Update contact name if provided
        if ($pushName && $conversation->contact_name !== $pushName) {
            $conversation->update(['contact_name' => $pushName]);
        }

        // Prevent duplicate messages
        if ($messageId && WhatsAppMessage::where('message_id', $messageId)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        // Create message
        WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'message_id' => $messageId,
            'type' => $type,
            'body' => $body,
            'media_url' => $mediaUrl,
            'media_mimetype' => $mimetype,
            'media_filename' => $filename,
            'from_me' => $fromMe,
            'status' => 'delivered',
            'raw_data' => $messageData,
            'message_at' => now(),
        ]);

        // Update conversation
        $conversation->update([
            'last_message' => $body ? mb_substr($body, 0, 255) : "[{$type}]",
            'last_message_at' => now(),
            'unread_count' => $fromMe ? $conversation->unread_count : $conversation->unread_count + 1,
        ]);

        return response()->json(['status' => 'processed']);
    }

    private function handleMessagesUpdate(WhatsAppInstance $instance, array $data): JsonResponse
    {
        $updates = $data['data'] ?? [];

        if (!is_array($updates)) {
            return response()->json(['status' => 'ok']);
        }

        // Handle single update or array of updates
        $items = isset($updates['key']) ? [$updates] : $updates;

        foreach ($items as $update) {
            $messageId = $update['key']['id'] ?? null;
            $status = $update['update']['status'] ?? null;

            if (!$messageId || !$status) continue;

            $statusMap = [
                'DELIVERY_ACK' => 'delivered',
                'READ' => 'read',
                'PLAYED' => 'read',
            ];

            $newStatus = $statusMap[$status] ?? null;

            if ($newStatus) {
                WhatsAppMessage::where('message_id', $messageId)
                    ->update(['status' => $newStatus]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleConnectionUpdate(WhatsAppInstance $instance, array $data): JsonResponse
    {
        $state = $data['data']['state'] ?? null;

        if ($state === 'open') {
            $instance->update([
                'status' => InstanceStatus::Connected,
                'qrcode' => null,
                'connected_at' => now(),
            ]);
        } elseif ($state === 'close') {
            $instance->update([
                'status' => InstanceStatus::Disconnected,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleQrcodeUpdated(WhatsAppInstance $instance, array $data): JsonResponse
    {
        $qrcode = $data['data']['qrcode']['base64'] ?? null;

        $instance->update([
            'status' => InstanceStatus::Connecting,
            'qrcode' => $qrcode,
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function extractPhone(string $jid): string
    {
        return explode('@', $jid)[0] ?? $jid;
    }

    private function parseMessage(array $message, array $messageData): array
    {
        $type = 'text';
        $body = null;
        $mediaUrl = null;
        $mimetype = null;
        $filename = null;

        if (isset($message['conversation'])) {
            $body = $message['conversation'];
        } elseif (isset($message['extendedTextMessage'])) {
            $body = $message['extendedTextMessage']['text'] ?? null;
        } elseif (isset($message['imageMessage'])) {
            $type = 'image';
            $body = $message['imageMessage']['caption'] ?? null;
            $mimetype = $message['imageMessage']['mimetype'] ?? null;
            $mediaUrl = $messageData['mediaUrl'] ?? null;
        } elseif (isset($message['audioMessage'])) {
            $type = 'audio';
            $mimetype = $message['audioMessage']['mimetype'] ?? null;
            $mediaUrl = $messageData['mediaUrl'] ?? null;
        } elseif (isset($message['videoMessage'])) {
            $type = 'video';
            $body = $message['videoMessage']['caption'] ?? null;
            $mimetype = $message['videoMessage']['mimetype'] ?? null;
            $mediaUrl = $messageData['mediaUrl'] ?? null;
        } elseif (isset($message['documentMessage'])) {
            $type = 'document';
            $filename = $message['documentMessage']['fileName'] ?? null;
            $mimetype = $message['documentMessage']['mimetype'] ?? null;
            $mediaUrl = $messageData['mediaUrl'] ?? null;
        } elseif (isset($message['stickerMessage'])) {
            $type = 'sticker';
            $mediaUrl = $messageData['mediaUrl'] ?? null;
        } elseif (isset($message['locationMessage'])) {
            $type = 'location';
            $lat = $message['locationMessage']['degreesLatitude'] ?? '';
            $lng = $message['locationMessage']['degreesLongitude'] ?? '';
            $body = "{$lat},{$lng}";
        }

        return [$type, $body, $mediaUrl, $mimetype, $filename];
    }
}
