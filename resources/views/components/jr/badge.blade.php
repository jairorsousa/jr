@props([
    'variant' => 'neutral',
    'size' => 'default',
])

@php
    $variantClasses = match($variant) {
        'up' => 'text-up bg-up-bg',
        'down' => 'text-down bg-down-bg',
        'success' => 'text-success bg-success-bg',
        'error' => 'text-error bg-down-bg',
        'info' => 'text-info bg-info-bg',
        'primary' => 'text-primary-500 bg-primary-100',
        'neutral' => 'text-mono-600 bg-mono-100',
        default => 'text-mono-600 bg-mono-100',
    };

    $sizeClasses = match($size) {
        'sm' => 'text-[10px] px-2 py-0.5',
        default => 'text-xs px-2.5 py-1',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-semibold rounded-pill $variantClasses $sizeClasses"]) }}>
    {{ $slot }}
</span>
