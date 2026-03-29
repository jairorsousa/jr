<?php

namespace App\Models;

use App\Enums\RecurrenceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'is_all_day',
        'location',
        'color',
        'reminder_minutes',
        'is_recurring',
        'recurrence_type',
        'recurrence_end',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_all_day' => 'boolean',
            'reminder_minutes' => 'integer',
            'is_recurring' => 'boolean',
            'recurrence_type' => RecurrenceType::class,
            'recurrence_end' => 'date',
        ];
    }
}
