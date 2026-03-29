<?php

namespace App\Livewire\Agenda;

use App\Enums\RecurrenceType;
use App\Enums\TransactionType;
use App\Models\Event;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Calendario extends Component
{
    public string $currentMonth;
    public string $view = 'month'; // month, week, day

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showDetailModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public ?array $detailEvent = null;

    // Form fields
    public string $title = '';
    public string $description = '';
    public string $start_date = '';
    public string $start_time = '09:00';
    public string $end_date = '';
    public string $end_time = '10:00';
    public bool $is_all_day = false;
    public string $location = '';
    public string $color = '#ff6f00';
    public string $reminder_minutes = '';
    public bool $is_recurring = false;
    public string $recurrence_type = 'weekly';
    public string $recurrence_end = '';

    // Week/day view reference
    public string $selectedDate = '';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date',
            'start_time' => $this->is_all_day ? 'nullable' : 'required|date_format:H:i',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'color' => 'required|string|max:20',
            'reminder_minutes' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|string|in:daily,weekly,monthly,yearly',
            'recurrence_end' => 'nullable|date|after:start_date',
        ];
    }

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->selectedDate = now()->format('Y-m-d');
    }

    // Navigation
    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    // CRUD
    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
        $this->start_date = $date ?? now()->format('Y-m-d');
        $this->end_date = $this->start_date;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $event = Event::findOrFail($id);
        $this->editingId = $id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->start_date = $event->start_at->format('Y-m-d');
        $this->start_time = $event->start_at->format('H:i');
        $this->end_date = $event->end_at?->format('Y-m-d') ?? $this->start_date;
        $this->end_time = $event->end_at?->format('H:i') ?? '10:00';
        $this->is_all_day = $event->is_all_day;
        $this->location = $event->location ?? '';
        $this->color = $event->color;
        $this->reminder_minutes = $event->reminder_minutes !== null ? (string) $event->reminder_minutes : '';
        $this->is_recurring = $event->is_recurring;
        $this->recurrence_type = $event->recurrence_type?->value ?? 'weekly';
        $this->recurrence_end = $event->recurrence_end?->format('Y-m-d') ?? '';
        $this->showDetailModal = false;
        $this->showModal = true;
    }

    public function showDetail(string $id): void
    {
        $event = Event::findOrFail($id);
        $this->detailEvent = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'start_at' => $event->start_at,
            'end_at' => $event->end_at,
            'is_all_day' => $event->is_all_day,
            'location' => $event->location,
            'color' => $event->color,
            'is_recurring' => $event->is_recurring,
            'recurrence_type' => $event->recurrence_type?->label(),
        ];
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $startAt = $this->is_all_day
            ? Carbon::parse($this->start_date)->startOfDay()
            : Carbon::parse("{$this->start_date} {$this->start_time}");

        $endAt = null;
        if ($this->end_date) {
            $endAt = $this->is_all_day
                ? Carbon::parse($this->end_date)->endOfDay()
                : ($this->end_time ? Carbon::parse("{$this->end_date} {$this->end_time}") : null);
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location ?: null,
            'color' => $this->color,
            'reminder_minutes' => $this->reminder_minutes !== '' ? (int) $this->reminder_minutes : null,
            'is_recurring' => $this->is_recurring,
            'recurrence_type' => $this->is_recurring ? $this->recurrence_type : null,
            'recurrence_end' => $this->is_recurring && $this->recurrence_end ? $this->recurrence_end : null,
        ];

        if ($this->editingId) {
            Event::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Evento atualizado com sucesso.');
        } else {
            Event::create($data);
            session()->flash('success', 'Evento criado com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
        $this->showDetailModal = false;
    }

    public function delete(): void
    {
        Event::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Evento excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'start_date', 'end_date', 'location', 'reminder_minutes', 'recurrence_end']);
        $this->start_time = '09:00';
        $this->end_time = '10:00';
        $this->is_all_day = false;
        $this->color = '#ff6f00';
        $this->is_recurring = false;
        $this->recurrence_type = 'weekly';
        $this->resetValidation();
    }

    /**
     * Generate visual occurrences for recurring events without duplicating in DB.
     */
    private function getEventsForRange(Carbon $start, Carbon $end): array
    {
        $events = Event::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_at', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  // Recurring events that started before range
                  $q2->where('is_recurring', true)
                     ->where('start_at', '<=', $end)
                     ->where(function ($q3) use ($start) {
                         $q3->whereNull('recurrence_end')
                            ->orWhere('recurrence_end', '>=', $start);
                     });
              });
        })->get();

        $result = [];

        foreach ($events as $event) {
            if (!$event->is_recurring) {
                $result[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_at->format('Y-m-d'),
                    'start_at' => $event->start_at,
                    'end_at' => $event->end_at,
                    'color' => $event->color,
                    'is_all_day' => $event->is_all_day,
                    'is_financial' => false,
                ];
                continue;
            }

            // Generate recurring occurrences
            $recEnd = $event->recurrence_end
                ? Carbon::parse($event->recurrence_end)->min($end)
                : $end;

            $interval = match ($event->recurrence_type) {
                RecurrenceType::Daily => '1 day',
                RecurrenceType::Weekly => '1 week',
                RecurrenceType::Monthly => '1 month',
                RecurrenceType::Yearly => '1 year',
                default => '1 month',
            };

            $period = CarbonPeriod::create($event->start_at, $interval, $recEnd);

            foreach ($period as $date) {
                if ($date->between($start, $end)) {
                    $result[] = [
                        'id' => $event->id,
                        'title' => $event->title,
                        'date' => $date->format('Y-m-d'),
                        'start_at' => $date->copy()->setTimeFrom($event->start_at),
                        'end_at' => $event->end_at ? $date->copy()->setTimeFrom($event->end_at) : null,
                        'color' => $event->color,
                        'is_all_day' => $event->is_all_day,
                        'is_financial' => false,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Get pending financial transactions as calendar events.
     */
    private function getFinancialEvents(Carbon $start, Carbon $end): array
    {
        return Transaction::with('category')
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => "R$ " . number_format($t->amount, 2, ',', '.') . " - {$t->description}",
                'date' => $t->due_date->format('Y-m-d'),
                'start_at' => $t->due_date->startOfDay(),
                'end_at' => null,
                'color' => $t->type === TransactionType::Income ? '#15a96f' : '#e43b3b',
                'is_all_day' => true,
                'is_financial' => true,
                'transaction_id' => $t->id,
            ])
            ->toArray();
    }

    public function render()
    {
        $ref = Carbon::parse($this->currentMonth . '-01');
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));

        // Calendar grid range (include days from prev/next month to fill grid)
        $startOfMonth = $ref->copy()->startOfMonth();
        $endOfMonth = $ref->copy()->endOfMonth();
        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        // Events
        $events = $this->getEventsForRange($gridStart, $gridEnd);
        $financialEvents = $this->getFinancialEvents($gridStart, $gridEnd);
        $allEvents = array_merge($events, $financialEvents);

        // Group by date
        $eventsByDate = [];
        foreach ($allEvents as $ev) {
            $eventsByDate[$ev['date']][] = $ev;
        }

        // Build calendar days
        $weeks = [];
        $current = $gridStart->copy();
        while ($current <= $gridEnd) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dateStr = $current->format('Y-m-d');
                $week[] = [
                    'date' => $dateStr,
                    'day' => $current->day,
                    'isToday' => $current->isToday(),
                    'isCurrentMonth' => $current->month === $ref->month,
                    'events' => $eventsByDate[$dateStr] ?? [],
                ];
                $current->addDay();
            }
            $weeks[] = $week;
        }

        // Events for selected date (week/day view)
        $selectedDateEvents = $eventsByDate[$this->selectedDate] ?? [];

        $recurrenceTypes = RecurrenceType::cases();

        return view('livewire.agenda.calendario', compact(
            'weeks', 'monthLabel', 'selectedDateEvents', 'recurrenceTypes'
        ));
    }
}
