@props(['alerts' => []])

@if(count($alerts) > 0)
<div class="mb-3" data-cc-widget="alert-bar" data-cc-interval="30">
    @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['color'] ?? 'danger' }} alert-dismissible fade show py-2 mb-1 cc-alert" role="alert">
            <i class="bi {{ $alert['icon'] ?? 'bi-exclamation-circle' }} me-1"></i>
            <strong>{{ $alert['judul'] ?? '' }}</strong> {{ $alert['deskripsi'] ?? '' }}
            @if(!empty($alert['tautan']))
                <a href="{{ $alert['tautan'] }}" class="alert-link">Lihat</a>
            @endif
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
</div>
@endif
