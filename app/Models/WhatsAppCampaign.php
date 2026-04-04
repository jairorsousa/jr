<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCampaign extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'name',
        'instance_id',
        'template_id',
        'status',
        'custom_message',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'delay_seconds',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'delay_seconds' => 'integer',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WhatsAppInstance::class, 'instance_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
    }

    public function pendingRecipients(): HasMany
    {
        return $this->recipients()->where('status', 'pending');
    }

    public function sentRecipients(): HasMany
    {
        return $this->recipients()->where('status', 'sent');
    }

    public function failedRecipients(): HasMany
    {
        return $this->recipients()->where('status', 'failed');
    }

    public function isDraft(): bool
    {
        return $this->status === CampaignStatus::Draft;
    }

    public function isSending(): bool
    {
        return $this->status === CampaignStatus::Sending;
    }

    public function isPaused(): bool
    {
        return $this->status === CampaignStatus::Paused;
    }

    public function isCompleted(): bool
    {
        return $this->status === CampaignStatus::Completed;
    }

    public function progressPercent(): int
    {
        if ($this->total_recipients === 0) return 0;
        return (int) round(($this->sent_count + $this->failed_count) / $this->total_recipients * 100);
    }
}
