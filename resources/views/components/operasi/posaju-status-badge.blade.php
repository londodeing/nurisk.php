@props(['status' => 'direncanakan', 'size' => 'sm'])

<?php
$colors = [
    'direncanakan' => 'bg-yellow-100 text-yellow-700',
    'aktif' => 'bg-green-100 text-green-700',
    'diperpanjang' => 'bg-blue-100 text-blue-700',
    'ditutup' => 'bg-slate-100 text-slate-700',
];

$labels = [
    'direncanakan' => 'Direncanakan',
    'aktif' => 'Aktif',
    'diperpanjang' => 'Diperpanjang',
    'ditutup' => 'Ditutup',
];

$sizeClasses = [
    'xs' => 'px-1.5 py-0.5 text-[10px]',
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-base',
];
?>

<span class="inline-flex items-center rounded-full font-semibold {{ $colors[$status] ?? $colors['direncanakan'] }} {{ $sizeClasses[$size] ?? $sizeClasses['sm'] }}">
    {{ $labels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>