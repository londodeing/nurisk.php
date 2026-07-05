<x-app-layout>
    <x-slot name="header">Dashboard PCNU</x-slot>
    <x-slot name="breadcrumb">Home / Dashboard PCNU</x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-kpi-card title="Insiden Aktif" :value="$initialData['kpi']['posko_aktif'] ?? '-'" subtext="PCNU scope" icon="bi-exclamation-triangle" color="red" />
            <x-kpi-card title="Relawan Aktif" :value="$initialData['kpi']['relawan_aktif'] ?? '-'" subtext="Dalam penugasan" icon="bi-people" color="blue" />
            <x-kpi-card title="Stok Kritis" :value="$initialData['kpi']['stok_kritis'] ?? '-'" subtext="" icon="bi-box-seam" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Insiden Aktif</h3>
                        <a href="{{ route('insiden.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse(\App\Models\OperasiInsiden::aktif()->with('jenisBencana')->where('id_pcnu', auth()->user()->default_scope_id)->latest('dibuat_pada')->take(10)->get() as $insiden)
                        <a href="{{ route('insiden.show', $insiden) }}" class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <x-badge-status :status="$insiden->status_insiden" map="insiden" />
                                <div>
                                    <span class="text-sm font-medium text-gray-800">{{ $insiden->kode_kejadian }}</span>
                                    <span class="text-xs text-gray-400 ml-2">{{ $insiden->jenisBencana?->nama_bencana }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-badge-status :status="$insiden->prioritas" map="prioritas" />
                                <span class="text-xs text-gray-400">{{ $insiden->waktu_mulai ? \Carbon\Carbon::parse($insiden->waktu_mulai)->locale('id')->isoFormat('D MMM') : '' }}</span>
                            </div>
                        </a>
                        @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">
                            <span class="text-2xl block mb-2">🛡️</span> Tidak ada insiden aktif
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Antrian PCNU</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @php
                        $queues = [];
                        $queues[] = ['label' => 'Laporan baru masuk', 'count' => $initialData['kpi']['posko_aktif'] ?? 0, 'url' => route('dashboard.laporan.index'), 'icon' => 'bi-inbox'];
                        $queues[] = ['label' => 'TRC menunggu approval', 'count' => 0, 'url' => route('admin.approval.index'), 'icon' => 'bi-person-check'];
                        @endphp
                        @forelse($queues as $q)
                        <a href="{{ $q['url'] }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                            <i class="bi {{ $q['icon'] }} text-lg text-gray-400"></i>
                            <span class="text-sm text-gray-700 flex-1">{{ $q['label'] }}</span>
                            @if($q['count'] > 0)
                            <span class="text-sm font-semibold text-gray-900">{{ $q['count'] }}</span>
                            @endif
                        </a>
                        @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">✅ Semua bersih</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
