@props([
    'variant' => 'info',
    'dismissible' => true,
])

@php
    $config = match($variant) {
        'error' => ['bg' => 'bg-down-bg', 'text' => 'text-error', 'border' => 'border-error/20', 'icon' => 'error'],
        'success' => ['bg' => 'bg-success-bg', 'text' => 'text-success', 'border' => 'border-success/20', 'icon' => 'check_circle'],
        'info' => ['bg' => 'bg-info-bg', 'text' => 'text-info', 'border' => 'border-info/15', 'icon' => 'info'],
        default => ['bg' => 'bg-info-bg', 'text' => 'text-info', 'border' => 'border-info/15', 'icon' => 'info'],
    };
@endphp

<div x-data="{ visible: true }"
     x-show="visible"
     x-transition
     {{ $attributes->merge(['class' => "flex items-center gap-3 px-4 py-3 rounded-xl border {$config['bg']} {$config['text']} {$config['border']}"]) }}
>
    <span class="material-icons-outlined text-[20px] flex-shrink-0">{{ $config['icon'] }}</span>
    <span class="flex-1 text-sm font-medium">{{ $slot }}</span>
    @if($dismissible)
        <button @click="visible = false" class="flex-shrink-0 p-0.5 rounded hover:opacity-70 transition-opacity">
            <span class="material-icons-outlined text-[18px]">close</span>
        </button>
    @endif
</div>
