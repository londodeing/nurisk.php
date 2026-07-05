@props(['items' => []])

<div class="card shadow-sm mb-3" data-cc-widget="decision-queue" data-cc-interval="30">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold"><i class="bi bi-inbox me-1"></i>Decision Queue</span>
        <span class="badge bg-danger rounded-pill cc-queue-count">{{ count($items) }}</span>
    </div>
    <div class="card-body p-2 cc-queue-body">
        @forelse($items as $item)
            <div class="d-flex align-items-start p-2 border-bottom {{ $loop->last ? 'border-bottom-0' : '' }} cc-queue-item" data-severity="{{ $item['severity'] ?? 'normal' }}">
                <span class="me-2 mt-1">
                    @switch($item['severity'] ?? 'normal')
                        @case('critical') <i class="bi bi-exclamation-circle-fill text-danger"></i> @break
                        @case('high') <i class="bi bi-exclamation-triangle-fill text-warning"></i> @break
                        @default <i class="bi bi-info-circle-fill text-info"></i>
                    @endswitch
                </span>
                <div class="flex-grow-1">
                    <div class="fw-semibold small">{{ $item['judul'] ?? '' }}</div>
                    <div class="text-muted small">{{ $item['deskripsi'] ?? '' }}</div>
                    @if(!empty($item['aksi_tersedia']))
                        <div class="mt-1">
                            @foreach($item['aksi_tersedia'] as $aksi)
                                <a href="{{ $aksi['route'] ?? '#' }}" class="btn btn-{{ $aksi['color'] ?? 'primary' }} btn-sm me-1">
                                    <i class="bi {{ $aksi['icon'] ?? '' }}"></i> {{ $aksi['label'] ?? '' }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @isset($item['waktu'])
                    <x-dashboard-freshness-badge :timestamp="$item['waktu']" />
                @endisset
            </div>
        @empty
            <div class="text-center text-muted py-3 small">
                <i class="bi bi-check-circle text-success"></i> Tidak ada keputusan yang menunggu
            </div>
        @endforelse
    </div>
    <div class="card-footer bg-white py-1 text-end text-muted small cc-queue-ts">
        Terakhir diperiksa: <span class="cc-last-checked">—</span>
    </div>
</div>
