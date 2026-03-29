@props([
    'label' => null,
    'icon' => null,
    'error' => null,
    'success' => false,
    'helper' => null,
    'type' => 'text',
])

@php
    $stateClasses = match(true) {
        !empty($error) => 'border-error focus-within:border-error focus-within:shadow-[0_0_0_3px_rgba(255,71,71,.1)]',
        $success => 'border-success focus-within:border-success focus-within:shadow-[0_0_0_3px_rgba(28,201,125,.1)]',
        default => 'border-mono-200 focus-within:border-primary-500 focus-within:shadow-[0_0_0_3px_rgba(255,111,0,.1)]',
    };
@endphp

<div class="w-full">
    @if($label)
        <label class="block text-sm font-medium text-mono-600 mb-1.5">{{ $label }}</label>
    @endif

    <div class="flex items-center bg-mono-white border rounded-pill px-4 h-12 gap-2.5 transition-all duration-200 {{ $stateClasses }}">
        @if($icon)
            <span class="material-icons-outlined text-[20px] text-mono-300 flex-shrink-0">{{ $icon }}</span>
        @endif

        <input type="{{ $type }}"
               {{ $attributes->merge(['class' => 'flex-1 bg-transparent border-none outline-none text-sm text-mono-900 placeholder:text-mono-300 p-0 focus:ring-0']) }}
        >

        @if($success)
            <span class="material-icons-outlined text-[20px] text-success flex-shrink-0">check_circle</span>
        @elseif(!empty($error))
            <span class="material-icons-outlined text-[20px] text-error flex-shrink-0">error</span>
        @endif
    </div>

    @if(!empty($error))
        <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $error }}</p>
    @elseif($helper)
        <p class="text-xs text-mono-600 mt-1.5 pl-4">{{ $helper }}</p>
    @endif
</div>
