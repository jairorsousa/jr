@props([
    'striped' => false,
])

<div {{ $attributes->merge(['class' => 'bg-mono-white rounded-2xl border border-mono-100 overflow-hidden']) }}>
    <div class="overflow-x-auto">
    <table class="w-full border-collapse min-w-[600px]">
        @isset($head)
            <thead>
                <tr class="bg-mono-50">
                    {{ $head }}
                </tr>
            </thead>
        @endisset

        <tbody class="{{ $striped ? '[&>tr:nth-child(even)]:bg-mono-50' : '' }}">
            {{ $slot }}
        </tbody>
    </table>
    </div>
</div>
