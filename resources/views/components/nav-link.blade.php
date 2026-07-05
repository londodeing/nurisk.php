@props(['active' => false, 'icon' => null])

@php
$classes = $active
    ? 'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg bg-gray-800 text-white border-l-4 border-green-500'
    : 'flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
    <i class="bi {{ $icon }} text-base w-5 text-center"></i>
    @endif
    <span>{{ $slot }}</span>
</a>
