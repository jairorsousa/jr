@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-mono-white rounded-2xl shadow-card border border-mono-100' . ($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
