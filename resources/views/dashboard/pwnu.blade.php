<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>
    <x-slot name="breadcrumb">Home / Dashboard</x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-kpi-card title="Insiden Aktif" :value="$insidenAktif" subtext="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMM') }}" icon="bi-exclamation-triangle" color="red" />
            <x-kpi-card title="Personel Tergerak" value="-" subtext="Hari ini" icon="bi-people" color="blue" />
            <x-kpi-card title="Pos Aju Aktif" value="-" subtext="Di semua insiden" icon="bi-geo-alt" color="green" />
            <x-kpi-card title="Stok Kritis" value="-" subtext="" icon="bi-box-seam" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Peta Insiden Aktif</h3>
                    <span class="text-xs text-gray-400">60 detik refresh</span>
                </div>
                <div id="map-dashboard" style="height:400px" class="bg-gray-50"></div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Perlu Perhatian</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @php
                        $needsAttention = [];
                        if ($laporanMenunggu > 0) {
                            $needsAttention[] = ['label' => "Laporan menunggu validasi", 'count' => $laporanMenunggu, 'url' => route('dashboard.laporan.index'), 'icon' => 'bi-inbox'];
                        }
                        @endphp
                        @forelse($needsAttention as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                            <i class="bi {{ $item['icon'] }} text-lg text-gray-400"></i>
                            <span class="text-sm text-gray-700 flex-1">{{ $item['label'] }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $item['count'] }}</span>
                        </a>
                        @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">
                            <span class="text-2xl block mb-2">✅</span> Semua bersih
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Jurnal Terbaru</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @php
                        $jurnals = \App\Models\OperasiJurnal::orderBy('id_jurnal', 'desc')->take(10)->get();
                        @endphp
                        @forelse($jurnals as $jurnal)
                        <div class="px-5 py-3">
                            <div class="flex items-center gap-2 text-sm">
                                <x-badge-status :status="$jurnal->kategori_event" map="insiden" />
                                <span class="text-gray-800 font-medium truncate">{{ $jurnal->judul_event }}</span>
                            </div>
                            @if($jurnal->deskripsi_event)
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Illuminate\Support\Str::limit($jurnal->deskripsi_event, 60) }}</p>
                            @endif
                        </div>
                        @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">
                            Belum ada aktivitas
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Akses Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @php
                $links = [
                    ['label' => 'Laporan Kejadian', 'icon' => 'bi-inbox', 'route' => route('dashboard.laporan.index')],
                    ['label' => 'Insiden', 'icon' => 'bi-exclamation-triangle', 'route' => route('insiden.index')],
                    ['label' => 'Surat', 'icon' => 'bi-envelope', 'route' => route('surat.index')],
                    ['label' => 'Pos Aju', 'icon' => 'bi-geo-alt', 'route' => route('posaju.index')],
                    ['label' => 'Sitrep', 'icon' => 'bi-journal-text', 'route' => route('sitrep.index')],
                    ['label' => 'Command Center', 'icon' => 'bi-display', 'route' => route('command-center')],
                ];
                @endphp
                @foreach($links as $link)
                <a href="{{ $link['route'] }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-center">
                    <i class="bi {{ $link['icon'] }} text-xl text-gray-500"></i>
                    <span class="text-xs font-medium text-gray-600">{{ $link['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('map-dashboard').setView([-7.15, 110.14], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    });
    </script>
    @endpush
</x-app-layout>
