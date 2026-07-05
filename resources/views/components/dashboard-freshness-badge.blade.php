@props(['timestamp' => null])

@php
    $color = 'secondary';
    $label = '—';
    if ($timestamp) {
        $minutes = now()->diffInMinutes($timestamp);
        $color = match(true) {
            $minutes < 15 => 'success',
            $minutes < 60 => 'warning',
            default => 'danger',
        };
        $label = match(true) {
            $minutes < 1 => 'baru saja',
            $minutes < 60 => $minutes . ' menit lalu',
            $minutes < 1440 => intdiv($minutes, 60) . ' jam lalu',
            default => intdiv($minutes, 1440) . ' hari lalu',
        };
    }
@endphp

<span class="badge bg-{{ $color }} cc-freshness" title="{{ $timestamp ? $timestamp->format('d M Y H:i') : 'Tidak ada data' }}">
    <i class="bi bi-clock"></i> {{ $label }}
</span>
