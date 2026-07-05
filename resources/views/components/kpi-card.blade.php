@props(['title', 'value', 'subtext' => null, 'icon' => null, 'color' => 'green', 'warning' => false])

@php
$iconColors = [
    'green'  => 'bg-green-100 text-green-600',
    'blue'   => 'bg-blue-100 text-blue-600',
    'orange' => 'bg-orange-100 text-orange-600',
    'red'    => 'bg-red-100 text-red-600',
    'gray'   => 'bg-gray-100 text-gray-600',
];
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-4 h-24 flex items-center gap-4">
    @if($icon)
    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg {{ $iconColors[$color] ?? $iconColors['green'] }}">
        <i class="bi {{ $icon }} text-xl"></i>
    </div>
    @endif
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium text-gray-500 truncate">{{ $title }}</p>
        <p class="text-3xl font-bold font-mono text-gray-900">{{ $value }}</p>
        @if($subtext)
        <p class="text-xs text-gray-400 mt-0.5 {{ $warning ? 'text-red-500 font-medium' : '' }}">{{ $subtext }}</p>
        @endif
    </div>
</div>
