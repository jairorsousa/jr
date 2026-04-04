<?php

namespace App\Models;

use App\Enums\TemplateCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplate extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'name',
        'body',
        'category',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'category' => TemplateCategory::class,
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(WhatsAppCampaign::class, 'template_id');
    }

    /**
     * Extract variable placeholders from the template body.
     * E.g., "Ola {nome}, sua empresa {empresa}" => ['nome', 'empresa']
     */
    public function getVariables(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->body, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Render the template body replacing variables.
     */
    public function render(array $variables = []): string
    {
        $text = $this->body;
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
}
