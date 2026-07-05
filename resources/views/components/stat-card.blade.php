@props(['title', 'value', 'icon' => 'bi-info-circle', 'color' => 'primary'])

@php
    $colorMap = [
        'primary' => 'text-blue-600 bg-blue-100',
        'success' => 'text-green-600 bg-green-100',
        'warning' => 'text-yellow-600 bg-yellow-100',
        'danger' => 'text-red-600 bg-red-100',
        'info' => 'text-cyan-600 bg-cyan-100',
        'dark' => 'text-slate-800 bg-slate-200',
    ];
    $iconColors = $colorMap[$color] ?? $colorMap['primary'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-center gap-4 transition-transform hover:-translate-y-1 hover:shadow-md h-full">
    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg {{ $iconColors }}">
        <i class="bi {{ $icon }} text-2xl"></i>
    </div>
    <div class="flex-1 min-w-0">
        <h6 class="text-xs font-semibold tracking-wider text-slate-500 uppercase truncate mb-1">{{ $title }}</h6>
        <h3 class="text-2xl font-bold text-slate-800">{{ $value }}</h3>
    </div>
</div>
