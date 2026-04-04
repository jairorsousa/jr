<?php

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use App\Models\WhatsAppTemplate;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200; // 2 hours max
    public int $tries = 1;

    public function __construct(
        public string $campaignId
    ) {}

    public function handle(EvolutionApiService $service): void
    {
        $campaign = WhatsAppCampaign::with(['instance', 'template'])->find($this->campaignId);

        if (!$campaign) {
            Log::error("Campaign not found: {$this->campaignId}");
            return;
        }

        if (!in_array($campaign->status, [CampaignStatus::Sending, CampaignStatus::Scheduled])) {
            Log::info("Campaign {$campaign->id} is not in a sendable state: {$campaign->status->value}");
            return;
        }

        $campaign->update([
            'status' => CampaignStatus::Sending,
            'started_at' => $campaign->started_at ?? now(),
        ]);

        $instanceName = $campaign->instance->instance_name;
        $template = $campaign->template;

        $recipients = $campaign->pendingRecipients()
            ->orderBy('created_at')
            ->get();

        foreach ($recipients as $recipient) {
            // Re-check campaign status (might have been paused/cancelled)
            $campaign->refresh();
            if ($campaign->status === CampaignStatus::Paused || $campaign->status === CampaignStatus::Cancelled) {
                Log::info("Campaign {$campaign->id} was {$campaign->status->value}, stopping.");
                return;
            }

            try {
                $messageText = $this->buildMessage($campaign, $template, $recipient);
                $number = $recipient->phone . '@s.whatsapp.net';

                $result = $service->sendText($instanceName, $number, $messageText);

                if ($result['success']) {
                    $messageId = $result['data']['key']['id'] ?? null;
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'message_id' => $messageId,
                    ]);
                    $campaign->increment('sent_count');
                } else {
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => $result['error'] ?? 'Erro desconhecido',
                    ]);
                    $campaign->increment('failed_count');
                }
            } catch (\Exception $e) {
                Log::error("Campaign send error: {$e->getMessage()}", [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                ]);
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $campaign->increment('failed_count');
            }

            // Delay between messages to avoid rate limiting
            if ($campaign->delay_seconds > 0) {
                sleep($campaign->delay_seconds);
            }
        }

        // Check completion
        $campaign->refresh();
        $stillPending = $campaign->pendingRecipients()->count();

        if ($stillPending === 0 && $campaign->status === CampaignStatus::Sending) {
            $campaign->update([
                'status' => CampaignStatus::Completed,
                'completed_at' => now(),
            ]);

            // Increment template usage count
            if ($template) {
                $template->increment('usage_count');
            }
        }

        Log::info("Campaign {$campaign->id} processed. Sent: {$campaign->sent_count}, Failed: {$campaign->failed_count}");
    }

    private function buildMessage(WhatsAppCampaign $campaign, ?WhatsAppTemplate $template, WhatsAppCampaignRecipient $recipient): string
    {
        if ($template) {
            $variables = $recipient->variables ?? [];
            // Auto-fill common variables
            $variables['nome'] = $variables['nome'] ?? $recipient->name ?? '';
            $variables['telefone'] = $variables['telefone'] ?? $recipient->phone ?? '';

            return $template->render($variables);
        }

        // Use custom message with basic variable replacement
        $text = $campaign->custom_message ?? '';
        $text = str_replace('{nome}', $recipient->name ?? '', $text);
        $text = str_replace('{telefone}', $recipient->phone ?? '', $text);

        return $text;
    }
}
