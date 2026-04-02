<x-app-layout>
    <x-slot name="header">WhatsApp - Conversas</x-slot>

    <livewire:whats-app.chat :instanceId="$instanceId ?? null" />
</x-app-layout>
