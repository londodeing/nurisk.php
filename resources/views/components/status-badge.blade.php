@props(['status'])

@php
    $color = match($status) {
        'aktif', 'selesai', 'aman' => 'success',
        'kritis', 'darurat' => 'danger',
        'pending', 'siaga' => 'warning',
        default => 'secondary'
    };
@endphp

<span class="badge bg-{{ $color }} text-uppercase">{{ $status }}</span>
