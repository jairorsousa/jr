@props([
    'variant' => 'primary',
    'size' => 'default',
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold text-sm border-none cursor-pointer rounded-pill transition-all duration-200 active:scale-[.97] focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClasses = match($size) {
        'sm' => 'h-9 px-4 text-[13px]',
        default => 'h-11 px-6',
    };

    $variantClasses = match($variant) {
        'primary' => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-2 focus:ring-primary-500/30',
        'standard' => 'bg-transparent text-mono-900 border border-mono-200 hover:bg-mono-50',
        'mono' => 'bg-mono-100 text-mono-900 hover:bg-mono-200',
        'text' => 'bg-transparent text-mono-900 hover:bg-mono-50',
        'danger' => 'bg-error text-white hover:bg-red-600 focus:ring-2 focus:ring-error/30',
        default => 'bg-primary-500 text-white hover:bg-primary-600',
    };

    $classes = "$baseClasses $sizeClasses $variantClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
